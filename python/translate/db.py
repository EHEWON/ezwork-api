import pymysql
import os
from dotenv import load_dotenv, find_dotenv
from threading import Lock

_ = load_dotenv(find_dotenv()) # read local .env file

def get_conn():
    mysql_host=os.environ['DB_HOST']
    mysql_port=os.environ['DB_PORT']
    mysql_db=os.environ['DB_DATABASE']
    mysql_user=os.environ['DB_USERNAME']
    mysql_password=os.environ['DB_PASSWORD']
    return pymysql.connect(host=mysql_host, port=int(mysql_port), user=mysql_user, passwd=mysql_password, db=mysql_db, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)

def execute(sql, *params):
    conn = get_conn()
    lock=Lock()
    lock.acquire()
    cursor=conn.cursor() 
    try:               
        cursor.execute(sql, params)
        conn.commit()
        lock.release()
        cursor.close()
        conn.close()
    except:
        lock.release()
        conn.rollback()


def get(sql, *params):
    conn=get_conn()
    lock=Lock()
    lock.acquire()
    try:
        cursor=conn.cursor(cursor=pymysql.cursors.DictCursor)        
        cursor.execute(sql, params)
        result=cursor.fetchone()
        lock.release()
        cursor.close()
        conn.close()
        return result
    except:
        lock.release()
        return []
