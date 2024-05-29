import threading
import openpyxl
import translate
import common
import os
import sys
import time
import datetime

def start(input_file,output_file,lang,model,system,processfile,output_url,threads):
    # 允许的最大线程
    if threads is None or int(threads)<0:
        max_threads=10
    else:
        max_threads=int(threads)
    # 当前执行的索引位置
    run_index=0
    start_time = datetime.datetime.now()
    wb = openpyxl.load_workbook(input_file) 
    sheets = wb.get_sheet_names()
    texts=[]
    for sheet in sheets:
        ws = wb.get_sheet_by_name(sheet)
        read_row(ws.rows, texts)
        
    # print(texts)
    max_run=max_threads if len(texts)>max_threads else len(texts)
    before_active_count=threading.activeCount()
    while run_index<=len(texts)-1:
        if threading.activeCount()<max_run+before_active_count:
            thread = threading.Thread(target=translate.get,args=(texts,run_index, lang,model,system,processfile,output_url))
            thread.start()
            run_index+=1
    
    while True:
        complete=True
        for text in texts:
            if not text['complete']:
                complete=False
        if complete:
            break
        else:
            time.sleep(1)

    text_count=0
    # print(texts)
    for sheet in sheets:
        ws = wb.get_sheet_by_name(sheet)
        text_count+=write_row(ws.rows, texts)

    wb.save(output_file)
    end_time = datetime.datetime.now()
    spend_time=common.display_spend(start_time, end_time)
    translate.complete(processfile,output_url,text_count,spend_time)
    return True,text_count,spend_time


def read_row(rows,texts):
    for row in rows:
        text=""
        for cell in row:
            value=cell.value
            if value!=None and not common.is_all_punc(value):
                if text=="":
                    text=value
                else:
                    text=text+"\n"+value
        if text!=None and not common.is_all_punc(text):
            texts.append({"text":text, "complete":False})

def write_row(rows, texts):
    text_count=0
    for row in rows:
        text=""
        for cell in row:
            value=cell.value
            if value!=None and not common.is_all_punc(value):
                if text=="":
                    text=value
                else:
                    text=text+"\n"+value
        if text!=None and not common.is_all_punc(text):
            item=texts.pop(0)
            values=item['text'].split("\n")
            text_count+=item['count']
            for cell in row:
                value=cell.value
                if value!=None and not common.is_all_punc(value):
                    if len(values)>0:
                        cell.value=values.pop(0)
    return text_count



