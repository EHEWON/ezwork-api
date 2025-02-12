import threading
import openai
import os
import sys
import time
import getopt
import translate
import word
import excel
import powerpoint
import pdf
import gptpdf
import txt
import csv_handle
import md
import pymysql
import db
import common
import traceback

# 当前正在执行的线程
run_threads=0

def main():
    global run_threads
    # 允许的最大线程
    max_threads=10
    # 当前执行的索引位置
    run_index=0
    # 是否保留原文
    keep_original=False
    # 要翻译的文件路径
    file_path=''
    # 翻译后的目标文件路径
    target_file=''
    uuid=sys.argv[1]
    storage_path=sys.argv[2]

    trans=db.get("select * from translate where uuid=%s", uuid)

    translate_id=trans['id']
    origin_filename=trans['origin_filename']
    origin_filepath=trans['origin_filepath']
    target_filepath=trans['target_filepath']
    api_key=trans['api_key']
    api_url=trans['api_url']

    file_path=os.path.join(storage_path, origin_filepath.lstrip("/"))
    target_file=os.path.join(storage_path, target_filepath.lstrip("/"))

    origin_path_dir=os.path.dirname(file_path)
    target_path_dir=os.path.dirname(target_file)

    if trans['status']=="done":
        sys.exit();
    
    if not os.path.exists(origin_path_dir):
        os.makedirs(origin_path_dir, mode=0o777, exist_ok=True)
    if not os.path.exists(target_path_dir):
        os.makedirs(target_path_dir, mode=0o777, exist_ok=True)

    trans['file_path']=file_path
    trans['target_file']=target_file
    trans['storage_path']=storage_path
    trans['target_path_dir']=target_path_dir
    extension = origin_filename[origin_filename.rfind('.'):]
    trans['extension']=extension
    trans['run_complete']=True
    item_count=0
    spend_time=''
    try:
        status=True
        # 设置OpenAI API
        translate.init_openai(api_url, api_key)
        if extension=='.docx' or extension=='.doc':
            status=word.start(trans)
        elif extension=='.xls' or extension == '.xlsx':
            status=excel.start(trans)
        elif extension=='.ppt' or extension == '.pptx':
            status=powerpoint.start(trans)
        elif extension == '.pdf':
            if pdf.is_scanned_pdf(trans['file_path']):
                status=gptpdf.start(trans)
            else:
                status=pdf.start(trans)
        elif extension == '.txt':
            status=txt.start(trans)
        elif extension == '.csv':
            status=csv_handle.start(trans)
        elif extension == '.md':
            status=md.start(trans)
        if status:
            print("success")
            # print(item_count + ";" + spend_time)
        else:
            print("翻译出错了")
    except Exception as e:
        translate.error(translate_id, str(e))
        exc_type, exc_value, exc_traceback = sys.exc_info()
        line_number = exc_traceback.tb_lineno  # 异常抛出的具体行号
        print(f"Error occurred on line: {line_number}")
        print(e)

if __name__ == '__main__':
    main()


