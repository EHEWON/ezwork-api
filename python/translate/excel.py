import threading
import openpyxl
import translate
import common
import os
import sys
import time
import datetime
import unicodedata

def start(trans):
    # 允许的最大线程
    threads=trans['threads']
    if threads is None or int(threads)<0:
        max_threads=10
    else:
        max_threads=int(threads)
    # 当前执行的索引位置
    run_index=0
    start_time = datetime.datetime.now()
    wb = openpyxl.load_workbook(trans['file_path']) 
    sheets = wb.get_sheet_names()
    texts=[]
    for sheet in sheets:
        ws = wb.get_sheet_by_name(sheet)
        read_row(ws.rows, texts)
        
    # print(texts)
    max_run=max_threads if len(texts)>max_threads else len(texts)
    before_active_count=threading.activeCount()
    event=threading.Event()
    while run_index<=len(texts)-1:
        if threading.activeCount()<max_run+before_active_count:
            if not event.is_set():
                thread = threading.Thread(target=translate.get,args=(trans,event,texts,run_index))
                thread.start()
                run_index+=1
            else:
                return False
    
    while True:
        complete=True
        for text in texts:
            if not text['complete']:
                complete=False
        if complete:
            break
        else:
            time.sleep(1)

    text_count = 0
    trans_type = trans['type']
    # print(texts)
    for sheet in sheets:
        ws = wb.get_sheet_by_name(sheet)        
        # 判断是哪种模式
        if trans_type.endswith("both_inherit"):
            text_count += write_row_both_inherit(ws, texts)
        elif trans_type.endswith("both_new"):
            text_count += write_row_both_new(ws, texts)
        elif trans_type.endswith("only_inherit"):
            text_count += write_row_only_inherit(ws, texts)
        elif trans_type.endswith("only_new"):
            text_count += write_row_only_new(ws, texts)
        else:
            raise Exception("unknown trans_type")

    wb.save(trans['target_file'])
    end_time = datetime.datetime.now()
    spend_time=common.display_spend(start_time, end_time)
    translate.complete(trans,text_count,spend_time)
    return True


def read_row(rows,texts):
    for row in rows:
        text=""
        for cell in row:
            value=cell.value
            if value!=None and not common.is_all_punc(value):
                texts.append({"text":value, "original_text":value, "complete":False})
        #         if text=="":
        #             text=value
        #         else:
        #             text=text+"\n"+value
        # if text!=None and not common.is_all_punc(text):
        #     texts.append({"text":text, "complete":False})
        
def write_row_only_inherit(ws, texts):
    """
    仅译文-继承：直接用译文覆盖，不调整行高。
    """
    text_count = 0
    for row in ws.rows:
        for cell in row:
            value = cell.value
            if value is not None and not common.is_all_punc(value) and texts:
                item = texts.pop(0)
                text_count += item.get('count', 0)
                cell.value = item['text']
    return text_count

def write_row_only_new(ws, texts):
    """
    仅译文-重排：用译文覆盖，并根据译文与原文长度比例调整行高。
    """
    text_count = 0
    rows_list = list(ws.rows)
    for row_index, row in enumerate(rows_list, start=1):
        max_ratio = 1
        for cell in row:
            value = cell.value
            if value is not None and not common.is_all_punc(value) and texts:
                item = texts.pop(0)
                text_count += item.get('count', 0)
                cell.value = item['text']
                ratio = calc_height_ratio(item['original_text'], item['text'])
                if ratio > max_ratio:
                    max_ratio = ratio
        if max_ratio > 1:
            current_height = ws.row_dimensions[row_index].height
            if current_height is None:
                current_height = 15
            ws.row_dimensions[row_index].height = current_height * max_ratio + 15
    return text_count

def write_row_both_inherit(ws, texts):
    """
    双语-继承：保留原文，在译文后追加原文（格式：原文 + 换行 + 译文），不调整行高。
    """
    text_count = 0
    for row in ws.rows:
        for cell in row:
            value = cell.value
            if value is not None and not common.is_all_punc(value) and texts:
                item = texts.pop(0)
                text_count += item.get('count', 0)
                bilingual_text = item['original_text'] + '\n' + item['text']
                cell.value = bilingual_text
    return text_count

def write_row_both_new(ws, texts):
    """
    双语-重排：保留原文，在译文后追加原文，并根据双语文本长度调整行高。
    """
    text_count = 0
    rows_list = list(ws.rows)
    for row_index, row in enumerate(rows_list, start=1):
        max_ratio = 1
        for cell in row:
            value = cell.value
            if value is not None and not common.is_all_punc(value) and texts:
                item = texts.pop(0)
                text_count += item.get('count', 0)
                bilingual_text = item['original_text'] + '\n' + item['text']
                ratio = calc_height_ratio(item['original_text'], bilingual_text)
                if ratio > max_ratio:
                    max_ratio = ratio
                cell.value = bilingual_text
        if max_ratio > 1:
            current_height = ws.row_dimensions[row_index].height
            if current_height is None:
                current_height = 15
            ws.row_dimensions[row_index].height = current_height * max_ratio + 15
    return text_count

def weighted_length(s):
    """
    计算字符串的加权长度：对于东亚全角字符（east_asian_width 为 'F' 或 'W'），计为2，其它字符计为1。
    """
    length = 0
    for ch in s:
        if unicodedata.east_asian_width(ch) in ('F', 'W'):
            length += 2
        else:
            length += 1
    return length

def calc_height_ratio(original_text, bilingual_text):
    """
    根据原文和双语文本的加权长度比例计算行高调整比例。
    如果原文为空，则返回1。
    """
    if not original_text:
        return 1
    original_len = weighted_length(original_text)
    bilingual_len = weighted_length(bilingual_text)
    return bilingual_len / original_len