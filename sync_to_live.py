# sync_to_live.py
import os
import urllib.request
import json
import decimal
import datetime
import mysql.connector

# Custom JSON encoder to handle decimals and datetimes
class CustomEncoder(json.JSONEncoder):
    def default(self, obj):
        if isinstance(obj, decimal.Decimal):
            return float(obj)
        if isinstance(obj, (datetime.date, datetime.datetime)):
            return obj.isoformat()
        return super(CustomEncoder, self).default(obj)

def sync():
    print("Reading local database...")
    try:
        # Connect to local database
        local_db = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='skinplus_db'
        )
        cursor = local_db.cursor(dictionary=True)
    except Exception as e:
        print(f"Error connecting to local database: {e}")
        return

    # Fetch rows
    data = {}
    
    # 1. Fetch products
    cursor.execute("SELECT * FROM products")
    data['products'] = cursor.fetchall()
    print(f"Loaded {len(data['products'])} products.")

    # 2. Fetch data_history
    cursor.execute("SELECT * FROM data_history")
    data['data_history'] = cursor.fetchall()
    print(f"Loaded {len(data['data_history'])} history rows.")

    # 3. Fetch history_logs
    cursor.execute("SELECT * FROM history_logs")
    data['history_logs'] = cursor.fetchall()
    print(f"Loaded {len(data['history_logs'])} log entries.")

    local_db.close()

    # Define URL and Secret Token
    url = "https://skinplus.space/sync_scraper.php"
    token = "plusMin1SecretToken"  # Default fallback
    
    # Load custom token from db_config.json if defined
    config_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'db_config.json')
    if os.path.exists(config_path):
        try:
            with open(config_path, 'r') as f:
                cfg = json.load(f)
                token = cfg.get('sync_token', token)
        except Exception:
            pass

    print(f"Sending data to {url}...")
    
    payload = json.dumps(data, cls=CustomEncoder).encode('utf-8')
    
    req = urllib.request.Request(
        url,
        data=payload,
        headers={
            'Content-Type': 'application/json',
            'X-Sync-Token': token
        },
        method='POST'
    )

    try:
        with urllib.request.urlopen(req) as response:
            res_body = response.read().decode('utf-8')
            res_data = json.loads(res_body)
            if res_data.get('success'):
                print("Sync Successful!")
                print("Synced counts:", res_data.get('synced'))
            else:
                print(f"Sync failed: {res_data.get('error')}")
    except Exception as e:
        print(f"Connection failed: {e}")

if __name__ == '__main__':
    sync()
