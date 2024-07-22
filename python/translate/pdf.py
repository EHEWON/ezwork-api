import threading
import fitz
import re
import translate
import common
import os
import sys
import time
import datetime
from urllib.parse import quote
import pdfkit
import subprocess
import platform
# from weasyprint import HTML

def start(trans):
    uuid=trans['uuid']
    html_path=trans['storage_path']+'/uploads/'+uuid+'.html'
    # remove_newlines_from_html(html_path)
    # exit()
    # print(trans['storage_path']+'/uploads/pdf.html')
    # HTML(trans['storage_path']+'/uploads/pdf.html').write_pdf(trans['storage_path']+'/uploads/pdf.pdf')
    # with open(html_path) as f:
    #     pdfkit.from_file(f, pdf_path,options={"enable-local-file-access":True})
    # exit()

    # translate.translate_html('<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>change account</title></head><body><p>Hello <strong>{{$user["email"]}}</strong>,</p><p>You are requesting a verification code change account , the verification code is valid for {{$user["expired"]}}, please use it as soon as possible. Please ignore this email if you are not doing it yourself.</p><p><strong>{{$user["code"]}}</strong></p><p>EHEWON - Cross-border Digital Supply Chain Service PlatForm.</p><p><a href="{{env("MALL_URL")}}" target="_blank">{{env("MALL_URL_NO_HTTP")}}</a></p><p><a href="{{env("MALL_URL")}}" target="_blank"><img style="width:128px" src="{{env("FILE_HOST")}}/group1/M00/00/00/rBFIw2I65zKAHbkFAADxj2CkeEU808.png"></a></p></body></html>',trans['model'])
    # subprocess.run(['pdf2htmlEX', '--dest-file', html_path, pdf_path])
    # exit()
    # 允许的最大线程
    # print(trans)
    # wkhtmltopdf_bin=common.find_command_location("wkhtmltopdf")
    threads=trans['threads']
    if threads is None or int(threads)<0:
        max_threads=10
    else:
        max_threads=int(threads)
    # 当前执行的索引位置
    run_index=0
    start_time = datetime.datetime.now()
    # print(f'Source pdf file: {} \n', trans['file_path'])
    src_pdf = fitz.open(trans['file_path'])

    texts=[]
    text_count=0

    read_page_html(src_pdf, texts)
    # print(texts)
    # exit()
    # # for page in src_pdf:
    # #     # 获取页面的文本块
    # #     dicts=page.get_text("json")
    # #     print(dicts)
    # # exit()
    # page_width=0
    # page_height=0

    # read_block_text(src_pdf,texts)
    # # read_block_text(src_pdf)
    # # return False
    # newpdf = fitz.open()
    # print_texts(texts)
    # write_block_text(src_pdf, newpdf, texts)

    # read_row(src_pdf, texts)
    # newpdf = fitz.open()
    # write_row(newpdf, texts, src_pdf[0].rect.width, src_pdf[0].rect.height);

    # print(texts)
    # print(trans['target_file'])
    # newpdf.save(trans['target_file'])
    # newpdf.close()
    src_pdf.close()
    # texts=[]
    # for p, page in enumerate(src_pdf):
    #     blocks = page.get_text('dict')['blocks']
    #     # 1.3 文字
    #     txt_blks = [b for b in blocks if b['type'] != 1]
    #     for txt in txt_blks:
    #         text_tmp = ''.join([s['text'] for l in txt['lines'] for s in l['spans']])
    #         text_tmp = re.sub('[@#$%^&*\'\"\n\r\t]', ' ', text_tmp).strip()
    #         append_text(text_tmp,"", texts)

        
    #     tables = page.find_tables()
    #     # data = []
    #     for table in tables.tables:
    #         rows=table.extract()
    #         for row in table.extract():
    #             for cell in row:
    #                 append_text(cell, texts)

    max_run=max_threads if len(texts)>max_threads else len(texts)
    event=threading.Event()
    before_active_count=threading.activeCount()
    while run_index<=len(texts)-1:
        if threading.activeCount()<max_run+before_active_count:
            if not event.is_set():
                print("run_index:",run_index)
                thread = threading.Thread(target=translate.get,args=(trans,event,texts,run_index))
                thread.start()
                run_index+=1
            else:
                return False
    
    while True:
        if event.is_set():
            return False
        complete=True
        for text in texts:
            if not text['complete']:
                complete=False
        if complete:
            break
        else:
            time.sleep(1)


    # print(texts)

    # new_pdf = fitz.open()
    # for p, page in enumerate(src_pdf):

    #     # 1.1 创建大小相同的新页面
    #     new_page = new_pdf.new_page(width=page.rect.width, height=page.rect.height)

    #     blocks = page.get_text('dict')['blocks']
    #     # 1.2 图片
    #     img_blks = [b for b in blocks if b['type'] == 1]
    #     for img in img_blks:
    #         new_page.insert_image(img['bbox'], stream=img['image'])

    #     # 1.3 文字
    #     txt_blks = [b for b in blocks if b['type'] != 1]
    #     for txt in txt_blks:
    #         text_tmp = ''.join([s['text'] for l in txt['lines'] for s in l['spans']])
    #         text_tmp = re.sub('[@#$%^&*\'\"\n\r\t]', ' ', text_tmp).strip()
    #         if check_text(text) and len(texts)>0:
    #             item=texts.pop(0)
    #             trans_text=item.get('text',"")
    #             # print(text_tmp)
    #             # print(trans_text)
    #             # print(txt['bbox'])
    #             draw_text_avoid_overlap(new_page, trans_text,txt['bbox'][0],txt['bbox'][1], 10)
    #             # new_page.insert_textbox(txt['bbox'], trans_text,fontsize=10)
    #             # new_page.set_text(trans_text)

        
    #     tables = page.find_tables()
    #     for table in tables.tables:
    #         rows=table.extract()
    #         bbox=table.bbox
    #         for ri,row in enumerate(rows):
    #             for ci,cell in enumerate(row):
    #                 if check_text(cell) and len(texts)>0:
    #                     item=texts.pop(0)
    #                     trans_text=item.get('text',"")
    #                     print(trans_text)
    #                     print(cell)
    #                     rows[ri][ci]=trans_text
    #         print(rows)
    #         print(bbox)
    #         draw_table(new_page, rows, bbox[0], bbox[1], bbox[2]-bbox[0], 12)




    write_to_html_file(html_path, texts)
    config = pdfkit.configuration(wkhtmltopdf="/usr/local/bin/wkhtmltopdf")
    with open(html_path) as f:
        pdfkit.from_file(f, trans['target_file'],options={"enable-local-file-access":True}, configuration=config)

    # print(trans['target_file'])
    # new_pdf.save(trans['target_file'])
    # new_pdf.close()
    # src_pdf.close()

    end_time = datetime.datetime.now()
    spend_time=common.display_spend(start_time, end_time)
    translate.complete(trans,text_count,spend_time)
    return True


# def read_to_html(pages):

def read_page_html(pages, texts):
    for page in pages:
        html=page.get_text("xhtml")
        # print(html)
        # print(html.decode('utf-8'))
        append_text(html,'', texts)
   

def write_to_html_file(html_path,texts):
    with open(html_path, 'w+') as f:
        f.write('<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body>')
        for item in texts:
            f.write(item.get("text", ""))
        f.write('</body></html>')
        f.close()

def read_block_text(pages,texts):
    text=""
    for page in pages:
        last_x0=0
        last_x1=0
        html=page.get_text("html")
        with open("test.html",'a+') as f:
            f.write(html)
            f.close()
        exit()
        for block in page.get_text("blocks"):
            current_x1=block[2]
            current_x0=block[0]
            # 对于每个文本块，分行并读取
            if block[5]==0 or abs(current_x1-last_x1)>12 or abs(current_x0-last_x0)>12:
                append_text(text, "", texts)
                text=block[4].replace("\n","")
            else:
                text=text+(block[4].replace("\n",""))
            last_x1=block[2]
            last_x0=block[0]
    append_text(text, "", texts)

def write_block_text(pages,newpdf,texts):
    text=""
    for page in pages:
        last_x0=0
        last_x1=0
        last_y0=0
        new_page = newpdf.new_page(width=page.rect.width, height=page.rect.height)
        font=fitz.Font("helv")
        for block in page.get_text("blocks"):
            current_x1=block[2]
            current_x0=block[0]
            current_y0=block[1]
            # 对于每个文本块，分行并读取
            if block[5]==0 or abs(current_x1-last_x1)>12 or abs(current_x0-last_x0)>12 and len(texts)>0:
                item=texts.pop(0)
                trans_text=item.get("text","")
                new_page.insert_text((last_x0,last_y0), trans_text, fontsize=12,fontname="Helvetica", overlay=False)
                text=block[4].replace("\n","")
            else:
                text=text+(block[4].replace("\n",""))
            last_x1=block[2]
            last_x0=block[0]
            last_y0=block[1]
    if check_text(text) and len(texts):
        new_page.insert_text((last_x0,last_y0), trans_text, fontsize=12, overlay=False)

def write_page_text(pages,newpdf,texts):
    for page in pages:
        text=page.get_text("text")
        new_page = newpdf.new_page(width=page.rect.width, height=page.rect.height)
        if check_text(text) and len(texts)>0:
            item=texts.pop(0)
            text=item.get("text","")
            new_page.insert_text((0,0), text, fontsize=12, overlay=False)

def read_row(pages,texts):
    text=""
    for page in pages:
        # 获取页面的文本块
        for block in page.get_text("blocks"):
            # 对于每个文本块，分行并读取
            if block[5]==0:
                append_text(text, block, texts)
                text=block[4]
            else:
                text=text+block[4]

def write_row(newpdf, texts, page_width, page_height):
    text_count=0
    new_page = newpdf.new_page(width=page_width, height=page_height)
    for text in texts:
        print(text['text'])
        # draw_text_avoid_overlap(new_page, text['text'],text['block'][0],text['block'][1], 16)
        new_page.insert_text((text['block'][0],text['block'][1]),text['text'], fontsize=16)
        return



def append_text(text, block, texts):
    if check_text(text):
        # print(text)
        texts.append({"text":text,"block":block, "complete":False})


def check_text(text):
    return text!=None and len(text)>0 and not common.is_all_punc(text) 

def draw_text_avoid_overlap(page, text, x, y, font_size):
    """
    在指定位置绘制文本，避免与现有文本重叠。
    """
    text_length = len(text) * font_size  # 估算文本长度
    while True:
        text_box = page.get_textbox((x, y, x + text_length, y + font_size))
        if not text_box:
            break  # 没有重叠的文本，退出循环
        y += font_size + 1  # 移动到下一个位置
 
    page.insert_text((x,y),text, fontsize=font_size)


def draw_table(page, table_data, x, y, width, cell_height):
    # 表格的列数
    cols = len(table_data[0])
    rows = len(table_data)
    
    # 绘制表格
    for i in range(rows):
        for j in range(cols):
            # 文字写入
            txt = table_data[i][j]
            page.insert_text((x, y), txt)
            # 绘制单元格边框 (仅边界线)
            # 左边
            page.draw_line((x, y),( x+width/cols, y), width=0.5)
            # 上边
            if i == 0:
                page.draw_line((x, y), (x, y+cell_height), width=0.5)
            # 右边
            if j == cols-1:
                page.draw_line((x+width/cols, y), (x+width/cols, y+cell_height), width=0.5)
            # 下边
            if i == rows-1:
                page.draw_line((x, y+cell_height), (x+width/cols, y+cell_height), width=0.5)
            # 移动到下一个单元格
            x += width/cols
        # 移动到下一行
        x = 0
        y += cell_height

def wrap_text(text, width):
    words = text.split(' ')
    lines = []
    line = ""
    for word in words:
        if len(line.split(' ')) >= width:
            lines.append(line)
            line = ""
        if len(line + word + ' ') <= width * len(word):
            line += word + ' '
        else:
            lines.append(line)
            line = word + ' '
    if line:
        lines.append(line)
    return lines


def is_paragraph(block):
    # 假设一个段落至少有两行
    if len(block) < 2:
        return False
    # 假设一个段落的行间隔较大
    if max([line.height for line in block]) / min([line.height for line in block]) > 1.5:
        return True
    return False

def is_next_line_continuation(page, current_line, next_line_index):
    # 判断下一行是否是当前行的继续
    print(current_line)
    print(next_line_index)
    return abs(next_line_index - current_line) < 0.1

def print_texts(texts):
    for item in texts:
        print(item.get("text"))


