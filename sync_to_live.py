
import os
import urllib.request
import json
import decimal
import datetime
import mysql.connector
from db_helper import get_db_connection, add_history_log

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
        with urllib.request.urlopen(req, timeout=15) as response:
            res_body = response.read().decode('utf-8')
            return json.loads(res_body)
    except Exception as e:
        return {"success": False, "error": str(e)}

def sync():
    print("Reading local database...")
    try:
        local_db = get_db_connection()
        cursor = local_db.cursor(dictionary=True)
    except Exception as e:
        print(f"Error connecting to local database: {e}")
        return


    add_history_log(local_db, 'SYNC_START', 'Live Database Sync', 'Local DB', 'Syncing started')


    cursor.execute("SELECT * FROM products")
    products = cursor.fetchall()

    total_products = len(products)
    print(f"Loaded local data: {total_products} products.")

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

    sync_success = True
    error_msg = ""


    batch_size = 500
    if total_products > 0:
        print(f"Uploading {total_products} products in batches of {batch_size}...")
        for i in range(0, total_products, batch_size):
            batch = products[i : i + batch_size]
            res = send_request(url, {"action": "sync", "products": batch}, token)
            if not res.get('success'):
                print(f"Failed uploading product batch {i//batch_size + 1}: {res.get('error')}")
                sync_success = False
                error_msg = f"Product upload failed: {res.get('error')}"
                break
            print(f"  Uploaded products {i+1} to {min(i+batch_size, total_products)}")
    else:
        print("No new products to upload.")


    if sync_success:
        add_history_log(local_db, 'SYNC_COMPLETE', 'Live Database Sync', 'Syncing', 'Completed successfully')
    else:
        add_history_log(local_db, 'SYNC_FAILED', 'Live Database Sync', 'Syncing', error_msg)


    cursor.execute("SELECT * FROM history_logs")
    history_logs = cursor.fetchall()
    total_logs = len(history_logs)


    if total_logs > 0:
        print(f"Uploading {total_logs} log entries...")
        res = send_request(url, {"action": "sync", "history_logs": history_logs}, token)
        if not res.get('success'):
            print(f"Failed uploading logs: {res.get('error')}")
            add_history_log(local_db, 'SYNC_FAILED', 'Live Database Sync', 'Syncing Logs', f"Log upload failed: {res.get('error')}")
            local_db.close()
            return
        print("  Uploaded logs.")


    if sync_success:
        print("Sync successful. Clearing local staging tables...")
        try:
            cursor.execute("DELETE FROM products")
            cursor.execute("DELETE FROM history_logs")
            local_db.commit()
            print("  Local staging tables cleared.")
        except Exception as e:
            print(f"⚠️ Warning: Failed to clear local staging tables: {e}")

    local_db.close()

    if sync_success:
        print("\nSync Successful! Your live website is updated.")
    else:
        print("\nSync Failed! Check local logs for details.")

if __name__ == '__main__':
    sync()
