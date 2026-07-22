import os
import json
import mysql.connector

def get_db_connection():
    dir_path = os.path.dirname(os.path.abspath(__file__))
    config_path = os.path.join(dir_path, 'db_config.json')
    
    if os.path.exists(config_path):
        try:
            with open(config_path, 'r') as f:
                config = json.load(f)
            return mysql.connector.connect(
                host=config.get('host', 'localhost'),
                user=config.get('user', 'root'),
                password=config.get('password', ''),
                database=config.get('database', 'skinplus_db')
            )
        except Exception as e:
            print(f"Error reading db_config.json: {e}. Falling back to local database.")
            

    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='skinplus_db'
    )

def add_history_log(db, action_type, target_identifier, old_value, new_value, admin_user='system'):
    """Inserts a record into the history_logs table."""
    try:
        cursor = db.cursor()
        sql = """INSERT INTO history_logs (action_type, target_identifier, old_value, new_value, admin_user) 
                 VALUES (%s, %s, %s, %s, %s)"""
        cursor.execute(sql, (action_type, target_identifier, old_value, new_value, admin_user))
        db.commit()
        cursor.close()
        return True
    except Exception as e:
        print(f"⚠️ Failed to write to history_logs: {e}")
        return False

