import openai
import datetime
import common
import traceback
import re

def get(event,texts, index, target_lang,model,system,processfile):
    text=texts[index]
    # 创建一个对话列表
    # print("翻译{}--开始".format(str(index)))
    # print(datetime.datetime.now())
    try:
        content=req(text['text'], target_lang, model, system)
        text['count']=count_text(text['text'])
        if check_translated(content):
            text['text']=content
        text['complete']=True
        # print("翻译{}--结束".format(str(index)))
        # print(text)
        # print(datetime.datetime.now())
    except openai.AuthenticationError as e:
        if not event.is_set():
            print("AuthenticationError")
        event.set()
    except openai.APIConnectionError as e:
        if not event.is_set():
            print("APIConnectionError")
        event.set()
    except openai.PermissionDeniedError as e:
        if not event.is_set():
            print("PermissionDeniedError")
        event.set()
    except openai.RateLimitError as e:
        if not event.is_set():
            print("RateLimitError")
        event.set()
    except openai.APIStatusError as e:
        if not event.is_set():
            print("APIStatusError")
            print(e.response)
        event.set()
    except Exception as e:
        print(e)
        # traceback.print_exc()
        text['complete']=True
        # print("translate error")
    texts[index]=text
    # print(text)
    process(texts, processfile)
    exit(0)

def req(text,target_lang,model,system):
    message = [
        {"role": "system", "content": system.replace("{target_lang}", target_lang)},
        {"role": "user", "content": text}
    ]
    # print(openai.base_url)
    # print(message)
    response = openai.chat.completions.create(
        model=model,  # 使用GPT-3.5版本
        messages=message
    )
    print(response.choices[0])
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

def process(texts, processfile):
    total=0
    complete=0
    for text in texts:
        total+=1
        if text['complete']:
            complete+=1
    with open(processfile, 'w') as f:
        if total!=complete:
            f.write(str(total)+"$$$"+str(complete))
        f.close()

def complete(processfile,text_count,spend_time):
    with open(processfile, 'w') as f:
        f.write("1$$$1$$$"+str(text_count)+"$$$"+spend_time)
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
    if content.startswith("Sorry, I cannot") or content.startswith("I'm sorry, I") or content.startswith("Sorry, I can't") or content.startswith("Sorry, I need more") or content.startswith("抱歉，无法翻译") or content.startswith("错误：提供的文本") or content.startswith("抱歉，无法理解") or content.startswith("无法翻译") or content.startswith("抱歉，我无法") or content.startswith("对不起，我无法") or content.startswith("ご指示の内容は") or content.startswith("申し訳ございません") or content.startswith("Простите，") or content.startswith("Извините,") or content.startswith("Lo siento,"):
        return False
    else:
        return True
