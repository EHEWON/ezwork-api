import openai
import datetime
import common
import traceback
import re
import os
import pymysql
from db  import dbconn

def get(trans, event,texts, index):
    if event.is_set():
        exit(0)
    translate_id=trans['id']
    target_lang=trans['lang']
    model=trans['model']
    backup_model=trans['backup_model']
    prompt=trans['prompt']
    process_file=trans['process_file']
    text=texts[index]
    # 创建一个对话列表
    # print("翻译{}--开始".format(str(index)))
    # print(datetime.datetime.now())
    try:
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
        # print(e)
        # traceback.print_exc()
        text['complete']=True
        # print("translate error")
    texts[index]=text
    # print(text)
    if not event.is_set():
        process(texts, process_file)
    exit(0)

def req(text,target_lang,model,prompt):
    message = [
        {"role": "system", "content": prompt.replace("{target_lang}", target_lang)},
        {"role": "user", "content": text}
    ]
    # print(openai.base_url)
    # print(message)
    response = openai.chat.completions.create(
        model=model,  # 使用GPT-3.5版本
        messages=message
    )
    # for choices in response.choices:
    #     print(choices.message.content)
    content=response.choices[0].message.content
    # print(content)
    return content

def check(model):
    try:
        message = [
            {"role": "system", "content": "hi"},
            {"role": "user", "content": "你现在能翻译吗？"}
        ]
        response = openai.chat.completions.create(
            model=model,  # 使用GPT-3.5版本
            messages=message
        )
        # print(model)
        # print(type(response))
        # print(response)
        return True
    except Exception as e:
        print(e)
        return False

def process(texts, process_file):
    total=0
    complete=0
    for text in texts:
        total+=1
        if text['complete']:
            complete+=1
    with open(process_file, 'w') as f:
        if total!=complete:
            f.write(str(total)+"$$$"+str(complete))
        f.close()

def complete(trans,text_count,spend_time):
    conn=dbconn()
    cursor=conn.cursor()
    target_filesize=os.stat(trans['target_file']).st_size
    cursor.execute("update translate set status='done',end_at=now(),target_filesize=%s,word_count=%s where id=%s", (target_filesize,text_count, trans['id']))
    conn.commit()
    cursor.close()
    with open(trans['process_file'], 'w') as f:
        f.write("1$$$1$$$"+str(text_count)+"$$$"+spend_time)
        f.close()

def error(translate_id,process_file, message):
    conn=dbconn()
    cursor=conn.cursor()
    cursor.execute("update translate set failed_count=failed_count+1,status='failed',end_at=now(),failed_reason=%s where id=%s", (message, translate_id))
    conn.commit()
    cursor.close()
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