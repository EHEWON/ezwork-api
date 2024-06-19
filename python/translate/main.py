import threading
import openai
import os
import sys
import time
import getopt
from dotenv import load_dotenv, find_dotenv
import translate
import word
import excel
import powerpoint
import pymysql

_ = load_dotenv(find_dotenv()) # read local .env file

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

    mysql_host=os.environ['DB_HOST']
    mysql_port=os.environ['DB_PORT']
    mysql_db=os.environ['DB_DATABASE']
    mysql_user=os.environ['DB_USERNAME']
    mysql_password=os.environ['DB_PASSWORD']

    # 命令行参数
    conn = pymysql.connect(host=mysql_host, port=int(mysql_port), user=mysql_user, passwd=mysql_password, db=mysql_db, charset='utf8mb4') 
    cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
    cursor.execute("select * from translate where uuid=%s", (uuid,)) 
    t=cursor.fetchone()
    translate_id=t['id']
    origin_filename=t['origin_filename']
    origin_filepath=t['origin_filepath']
    target_filepath=t['target_filepath']
    api_key=t['api_key']
    api_url=t['api_url']
    # 系统提示词
    prompt=t['prompt']
    # openai模型
    model=t['model']
    threads=t['threads']
    # 目标语言
    lang=t['lang']

    file_path=storage_path+origin_filepath
    target_file=storage_path+target_filepath
    # 进度保存文件
    process_file= storage_path+"/process/"+uuid+".txt"

    extension = origin_filename[origin_filename.rfind('.'):]
    item_count=0
    spend_time=''
    try:
        # 设置OpenAI API
        translate.init_openai(api_url, api_key)
        if extension=='.docx':
            print(file_path)
            status,item_count,spend_time=word.start(file_path,target_file,lang,model,prompt,process_file,threads)
        elif extension=='.xls' or extension == '.xlsx':
            status,item_count,spend_time=excel.start(file_path,target_file,lang,model,prompt,process_file,threads)
        elif extension=='.ppt' or extension == '.pptx':
            status,item_count,spend_time=powerpoint.start(file_path,target_file,lang,model,prompt,process_file,threads)
        if status:
            print("success")
            cursor.execute("update translate set word_count=%s where id=%s", (item_count, translate_id))
            # print(item_count + ";" + spend_time)
        else:
            print("翻译出错了")
    except Exception as e:
        print(e)

    conn.commit()
    cursor.close()
    conn.close()

if __name__ == '__main__':
    main()


