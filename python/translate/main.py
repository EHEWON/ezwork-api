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
    # 默认为中文
    target_lang='中文'
    # 要翻译的文件路径
    file_path=''
    # 翻译后的目标文件路径
    target_file=''
    # openai模型
    model=""
    # 系统提示词
    system=""
    # 进度保存文件
    processfile=""
    # 输出文件地址
    output_url=""
    # openai的url和key
    api_key=""
    api_url=""

    # 进程数
    threads=10
    # 命令行参数
    short_opts = 'f:o:l:m:s'
    long_opts = ['file=', 'output=','lang=','model=','system=','processfile=','output_url=','threads=','api_url=','api_key=']
    try:
        args, remainder = getopt.getopt(sys.argv[1:], short_opts, long_opts)
    except getopt.GetoptError:
        print('参数错误')
        sys.exit(2)
    for opt, arg in args:
        if opt in ('-f', '--file'):
            # 指定要操作的Word文件路径
            file_path = arg
        elif opt in ('-o', '--output'):
            target_file=arg
        elif opt in ('-l', '--lang'):
            target_lang=arg
        elif opt in ('--model'):
            model=arg
        elif opt in ('-m','--model'):
            model=arg
        elif opt in ('-s','--system'):
            system=arg
        elif opt in ('--processfile'):
            processfile=arg
        elif opt in ('--output_url'):
            output_url=arg
        elif opt in ('--threads'):
            threads=arg
        elif opt in ('--api_url'):
            api_url=arg
        elif opt in ('--api_key'):
            api_key=arg

    if file_path is None or file_path == '':
        print("must set -f or --file")
        exit(2)

    if target_file is None or target_file == '':
        print("must set -o or --output")
        exit(2)

    extension = file_path[file_path.rfind('.'):]
    file_name = file_path[:file_path.rfind('.')]
    try:
        # 设置OpenAI API
        translate.init_openai(api_url, api_key)
        if extension=='.docx':
            status,item_count,spend_time=word.start(file_path,target_file,target_lang,model,system,processfile,threads)
        elif extension=='.xls' or extension == '.xlsx':
            status,item_count,spend_time=excel.start(file_path,target_file,target_lang,model,system,processfile,threads)
        elif extension=='.ppt' or extension == '.pptx':
            status,item_count,spend_time=powerpoint.start(file_path,target_file,target_lang,model,system,processfile,threads)
        if status:
            print("success")
            # print(item_count + ";" + spend_time)
        else:
            print("翻译出错了")
    except Exception as e:
        print("翻译出错了")
        print(e)


if __name__ == '__main__':
    main()


