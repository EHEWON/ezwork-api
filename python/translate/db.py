import pymysql
import os
from dotenv import load_dotenv, find_dotenv

_ = load_dotenv(find_dotenv()) # read local .env file

class dbconn():
    _instance=None
    _is_init=False

    def __new__(cls, *args, **kwargs):
        if cls._instance is None:
            mysql_host=os.environ['DB_HOST']
            mysql_port=os.environ['DB_PORT']
            mysql_db=os.environ['DB_DATABASE']
            mysql_user=os.environ['DB_USERNAME']
            mysql_password=os.environ['DB_PASSWORD']
            cls._instance = pymysql.connect(host=mysql_host, port=int(mysql_port), user=mysql_user, passwd=mysql_password, db=mysql_db, charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor) 
        return cls._instance

    def __init__(self):
        if self._is_init is False:
            self._is_init=True


    def get_cursor(self):
        return self._instance.cursor(cursor=pymysql.cursors.DictCursor)