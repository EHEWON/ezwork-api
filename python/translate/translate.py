import openai
# import tiktoken
import datetime
import common
import traceback
import re
import os
import pymysql
import db
from pathlib import Path
import logging

import sys

def get(trans, event,texts, index):
    if event.is_set():
        exit(0)
    translate_id=trans['id']
    target_lang=trans['lang']
    model=trans['model']
    backup_model=trans['backup_model']
    prompt=trans['prompt']
    process_file=trans['process_file']
    extension=trans['extension']
    text=texts[index]
    # print(text)
    # 创建一个对话列表
    # print("翻译{}--开始".format(str(index)))
    # print(datetime.datetime.now())
    try:
        if extension==".pdf":
            if text['type']=="text":
                content=translate_html(text['text'], target_lang, model, prompt)
            else:
                content=get_content_by_image(text['text'], target_lang)
        else:
            content=req(text['text'], target_lang, model, prompt)
        text['count']=count_text(text['text'])
        if check_translated(content):
            text['text']=content
        text['complete']=True
        # print("翻译{}--结束".format(str(index)))
        # print(text)
        # print(datetime.datetime.now())
    except openai.AuthenticationError as e:
        use_backup_model(trans, event,texts, index, "openai密钥或令牌无效")
    except openai.APIConnectionError as e:
        use_backup_model(trans, event,texts, index, "请求无法与openai服务器或建立安全连接")
    except openai.PermissionDeniedError as e:
        use_backup_model(trans, event,texts, index, "令牌额度不足")
    except openai.RateLimitError as e:
        use_backup_model(trans, event,texts, index, "访问速率达到限制,10分钟后再试")
    except openai.InternalServerError as e:
        use_backup_model(trans, event,texts, index, "当前分组上游负载已饱和，请稍后再试")
    except openai.APIStatusError as e:
        use_backup_model(trans, event,texts, index, e.response)
    except Exception as e:
        exc_type, exc_value, exc_traceback = sys.exc_info()
        line_number = exc_traceback.tb_lineno  # 异常抛出的具体行号
        print(f"Error occurred on line: {line_number}")
        print(e)
        # traceback.print_exc()
        text['complete']=True
        # print("translate error")
    texts[index]=text
    # print(text)
    if not event.is_set():
        process(texts, process_file,translate_id)
    exit(0)

def req(text,target_lang,model,prompt):
    message = [
        {"role": "system", "content": prompt.replace("{target_lang}", target_lang)},
        {"role": "user", "content": text}
    ]
    # print(openai.base_url)
    # print(message)
    # 禁用 OpenAI 的日志输出
    logging.getLogger("openai").setLevel(logging.WARNING)
    # 禁用 httpx 的日志输出
    logging.getLogger("httpx").setLevel(logging.WARNING)
    response = openai.chat.completions.create(
        model=model,  # 使用GPT-3.5版本
        messages=message
    )
    # for choices in response.choices:
    #     print(choices.message.content)
    content=response.choices[0].message.content
    # print(content)
    return content

def translate_html(html,target_lang,model,prompt):
    message = [
        {"role": "system", "content": "把下面的html翻译成{},只返回翻译后的内容".format(target_lang)},
        {"role": "user", "content": html}
    ]
    # print(openai.base_url)
    response = openai.chat.completions.create(
        model=model,  # 使用GPT-3.5版本
        messages=message
    )
    # for choices in response.choices:
    #     print(choices.message.content)
    content=response.choices[0].message.content
    return content

def get_content_by_image(base64_image,target_lang):
    # print(image_path)
    # file_object = openai.files.create(file=Path(image_path), purpose="这是一张图片")
    # print(file_object)
    message = [
        {"role": "system", "content": "你是一个图片ORC识别专家"},
        {"role": "user", "content": [
            {
                "type": "image_url",
                "image_url": {
                    "url": base64_image
                }
            },
            {
                "type": "text",
                # "text": "读取图片链接并提取其中的文本数据,只返回识别后的数据，将文本翻译成英文,并按照图片中的文字布局返回html。只包含body(不包含body本身)部分",
                # "text": f"提取图片中的所有文字数据，将提取的文本翻译成{target_lang},只返回原始文本和翻译结果",
                "text": f"提取图片中的所有文字数据,将提取的文本翻译成{target_lang},只返回翻译结果",
            }
        ]}
    ]
    # print(message)
    # print(openai.base_url)
    response = openai.chat.completions.create(
        model="gpt-4o",  # 使用GPT-3.5版本
        messages=message
    )
    # for choices in response.choices:
    #     print(choices.message.content)
    content=response.choices[0].message.content
    # return content
    # print(''.join(map(lambda x: f'<p>{x}</p>',content.split("\n"))))
    return ''.join(map(lambda x: f'<p>{x}</p>',content.split("\n")))

def check(model):
    try:
        message = [
            {"role": "system", "content": "你通晓世界所有语言,可以用来从一种语言翻译成另一种语言"},
            {"role": "user", "content": "你现在能翻译吗？"}
        ]
        response = openai.chat.completions.create(
            model=model,
            messages=message
        )
        return "OK"
    except openai.AuthenticationError as e:
        return "openai密钥或令牌无效"
    except openai.APIConnectionError as e:
        return "请求无法与openai服务器或建立安全连接"
    except openai.PermissionDeniedError as e:
        return "令牌额度不足"
    except openai.RateLimitError as e:
        return "访问速率达到限制,10分钟后再试"
    except openai.InternalServerError as e:
        return "当前分组上游负载已饱和，请稍后再试"
    except openai.APIStatusError as e:
        return e.response
    except Exception as e:
        return "当前无法完成翻译"

def process(texts, process_file, translate_id):
    total=0
    complete=0
    for text in texts:
        total+=1
        if text['complete']:
            complete+=1
    with open(process_file, 'w') as f:
        if total!=complete:
            f.write(str(total)+"$$$"+str(complete))
            if(total!=0):
                process=format((complete/total)*100, '.1f')
                db.execute("update translate set process=%s where id=%s", str(process), translate_id)
        f.close()

def complete(trans,text_count,spend_time):
    target_filesize=os.stat(trans['target_file']).st_size
    db.execute("update translate set status='done',end_at=now(),process=100,target_filesize=%s,word_count=%s where id=%s", target_filesize, text_count, trans['id'])
    with open(trans['process_file'], 'w') as f:
        f.write("1$$$1$$$"+str(text_count)+"$$$"+spend_time)
        f.close()

def error(translate_id,process_file, message):
    db.execute("update translate set failed_count=failed_count+1,status='failed',end_at=now(),failed_reason=%s where id=%s", message, translate_id)
    with open(process_file, 'w') as f:
        f.write("-1$$$"+message)
        f.close()

def count_text(text):
    count=0
    for char in text:
        if common.is_chinese(char):
            count+=1;
        elif char is None or char==" ":
            continue
        else:
            count+=0.5
    return count

def init_openai(url, key):
    openai.api_key = key
    if "v1" not in url:
        if url[-1]=="/":
            url+="v1/"
        else:
            url+="/v1/"
    openai.base_url = url

def check_translated(content):
    if content.startswith("Sorry, I cannot") or content.startswith("I am sorry,") or content.startswith("I'm sorry,") or content.startswith("Sorry, I can't") or content.startswith("Sorry, I need more") or content.startswith("抱歉，无法") or content.startswith("错误：提供的文本") or content.startswith("无法翻译") or content.startswith("抱歉，我无法") or content.startswith("对不起，我无法") or content.startswith("ご指示の内容は") or content.startswith("申し訳ございません") or content.startswith("Простите，") or content.startswith("Извините,") or content.startswith("Lo siento,"):
        return False
    else:
        return True


# def get_model_tokens(model,content):
#     encoding=tiktoken.encoding_for_model(model)
#     return en(encoding.encode(content))

def use_backup_model(trans, event,texts, index, message):
    if trans['backup_model']!=None and trans['backup_model']!="":
        trans['model']=trans['backup_model']
        trans['backup_model']=""
        get(trans, event,texts, index)
    else:
        if not event.is_set():
            error(trans['id'],trans['process_file'], message)
            print(message)
        event.set()