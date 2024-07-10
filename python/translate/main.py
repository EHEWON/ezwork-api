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
import pymysql
from db import dbconn

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

    conn=dbconn()
    cursor=conn.cursor(cursor=pymysql.cursors.DictCursor)
    cursor.execute("select * from translate where uuid=%s", (uuid,)) 
    trans=cursor.fetchone()
    cursor.close()
    translate_id=trans['id']
    origin_filename=trans['origin_filename']
    origin_filepath=trans['origin_filepath']
    target_filepath=trans['target_filepath']
    api_key=trans['api_key']
    api_url=trans['api_url']

    file_path=storage_path+origin_filepath
    target_file=storage_path+target_filepath
    # 进度保存文件
    process_file= storage_path+"/process/"+uuid+".txt"

    trans['file_path']=file_path
    trans['target_file']=target_file
    trans['process_file']=process_file

    extension = origin_filename[origin_filename.rfind('.'):]
    item_count=0
    spend_time=''
    try:
        # 设置OpenAI API
        translate.init_openai(api_url, api_key)
        if extension=='.docx':
            print(file_path)
            status=word.start(trans)
        elif extension=='.xls' or extension == '.xlsx':
            status=excel.start(trans)
        elif extension=='.ppt' or extension == '.pptx':
            status=powerpoint.start(trans)
        if status:
            print("success")
            # print(item_count + ";" + spend_time)
        else:
            print("翻译出错了")
    except Exception as e:
        translate.error(translate_id,process_file,str(e))
        print(e)
    conn.close()

if __name__ == '__main__':
    main()


