# sync_to_live.py
import os
import urllib.request
import json
import decimal
import datetime
import mysql.connector

class CustomEncoder(json.JSONEncoder):
    def default(self, obj):
        if isinstance(obj, decimal.Decimal):
            return float(obj)
        if isinstance(obj, (datetime.date, datetime.datetime)):
            return obj.isoformat()
        return super(CustomEncoder, self).default(obj)

def send_request(url, payload_dict, token):
    payload = json.dumps(payload_dict, cls=CustomEncoder).encode('utf-8')
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
            return json.loads(res_body)
    except Exception as e:
        return {"success": False, "error": str(e)}

def sync():
    print("Reading local database...")
    try:
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
    cursor.execute("SELECT * FROM products")
    products = cursor.fetchall()
    
    cursor.execute("SELECT * FROM data_history")
    data_history = cursor.fetchall()

    cursor.execute("SELECT * FROM history_logs")
    history_logs = cursor.fetchall()

    local_db.close()

    total_products = len(products)
    total_history = len(data_history)
    total_logs = len(history_logs)
    print(f"Loaded local data: {total_products} products, {total_history} history rows, {total_logs} log entries.")

    url = "https://skinplus.space/sync_scraper.php"
    token = "plusMin1SecretToken"
    
    config_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'db_config.json')
    if os.path.exists(config_path):
        try:
            with open(config_path, 'r') as f:
                cfg = json.load(f)
                token = cfg.get('sync_token', token)
        except Exception:
            pass

    # 1. Clear database on live server
    print("Clearing active tables on live database...")
    clear_res = send_request(url, {"action": "clear"}, token)
    if not clear_res.get('success'):
        print(f"Failed to clear database: {clear_res.get('error')}")
        return
    print("Live database cleared.")

    # 2. Upload products in batches of 500
    batch_size = 500
    print(f"Uploading {total_products} products in batches of {batch_size}...")
    for i in range(0, total_products, batch_size):
        batch = products[i : i + batch_size]
        res = send_request(url, {"action": "sync", "products": batch}, token)
        if not res.get('success'):
            print(f"Failed uploading product batch {i//batch_size + 1}: {res.get('error')}")
            return
        print(f"  Uploaded products {i+1} to {min(i+batch_size, total_products)}")

    # 3. Upload data_history in batches of 500
    print(f"Uploading {total_history} history rows in batches of {batch_size}...")
    for i in range(0, total_history, batch_size):
        batch = data_history[i : i + batch_size]
        res = send_request(url, {"action": "sync", "data_history": batch}, token)
        if not res.get('success'):
            print(f"Failed uploading history batch {i//batch_size + 1}: {res.get('error')}")
            return
        print(f"  Uploaded history {i+1} to {min(i+batch_size, total_history)}")

    # 4. Upload logs in one batch
    if total_logs > 0:
        print(f"Uploading {total_logs} log entries...")
        res = send_request(url, {"action": "sync", "history_logs": history_logs}, token)
        if not res.get('success'):
            print(f"Failed uploading logs: {res.get('error')}")
            return
        print("  Uploaded logs.")

    print("\nSync Successful! Your live website is updated.")

if __name__ == '__main__':
    sync()
