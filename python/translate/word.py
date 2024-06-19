import threading
from docx import Document
import translate
import common
import os
import sys
import time
import datetime

def start(input_file,output_file,lang,model,system,processfile,threads):
    # 允许的最大线程
    if threads is None or threads=="" or int(threads)<0:
        max_threads=10
    else:
        max_threads=int(threads)
    # 当前执行的索引位置
    run_index=0
    max_chars=1000
    start_time = datetime.datetime.now()
    # 创建Document对象，加载Word文件
    try:
        document = Document(input_file)
    except Exception as e:
        print(e)
        return False,0,""
    texts=[]
    # print("获取文本")
    # print(datetime.datetime.now())
    # 遍历所有段落进行修改
    for paragraph in document.paragraphs:
        read_run(paragraph.runs, texts)
        
        if len(paragraph.hyperlinks)>0:
            for hyperlink in paragraph.hyperlinks:
                read_run(hyperlink.runs, texts)

    # print("翻译文本--开始")
    # print(datetime.datetime.now())
    for table in document.tables:
        for row in table.rows:
            for cell in row.cells:
                for paragraph in cell.paragraphs:
                    read_run(paragraph.runs, texts)

                    if len(paragraph.hyperlinks)>0:
                        for hyperlink in paragraph.hyperlinks:
                            read_run(hyperlink.runs, texts)

    # print(texts)
    # exit()
    max_run=max_threads if len(texts)>max_threads else len(texts)
    event=threading.Event()
    before_active_count=threading.activeCount()
    while run_index<=len(texts)-1:
        if threading.activeCount()<max_run+before_active_count:
            if not event.is_set():
                thread = threading.Thread(target=translate.get,args=(event,texts,run_index, lang,model,system,processfile))
                thread.start()
                run_index+=1
            else:
                return False,0,""
    
    while True:
        complete=True
        for text in texts:
            if not text['complete']:
                complete=False
        if complete:
            break
        else:
            time.sleep(1)
    # print(texts)
    # print("翻译文本-结束")
    text_count=0
    current_texts=[]
    for paragraph in document.paragraphs:
        text_count+=write_run(paragraph.runs, texts)

        if len(paragraph.hyperlinks)>0:
            for hyperlink in paragraph.hyperlinks:
                text_count+=write_run(hyperlink.runs, texts)

    for table in document.tables:
        for row in table.rows:
            for cell in row.cells:
                for paragraph in cell.paragraphs:
                    text_count+=write_run(paragraph.runs, texts)

                    if len(paragraph.hyperlinks)>0:
                        for hyperlink in paragraph.hyperlinks:
                            text_count+=write_run(hyperlink.runs, texts)

    # print("编辑文档-结束")
    # print(datetime.datetime.now())
    document.save(output_file)
    end_time = datetime.datetime.now()
    spend_time=common.display_spend(start_time, end_time)
    translate.complete(processfile,text_count,spend_time)
    return True,text_count,spend_time



def read_run(runs,texts):
    text=""
    if len(runs)>0 or len(texts)==0:
        for run in runs:
            if run.text=="":
                if len(text)>0 and not common.is_all_punc(text):        
                    texts.append({"text":text, "complete":False})
                    text=""
            else:
                text+=run.text
        if len(text)>0 and not common.is_all_punc(text):
            texts.append({"text":text, "complete":False})


def write_run(runs,texts):
    text_count=0
    if len(runs)==0:
        return text_count
    text=""
    for index,run in enumerate(runs):
        if run.text=="":
            if len(text)>0 and not common.is_all_punc(text) and len(texts)>0:
                item=texts.pop(0)
                text_count+=item.get('count',0)
                runs[index-1].text=item.get('text',"")
                text=""
        else:
            text+=run.text
            run.text=""
    if len(text)>0 and not common.is_all_punc(text) and len(texts)>0:
        item=texts.pop(0)
        text_count+=item.get('count',0)
        runs[0].text=item.get('text',"")
    return text_count
