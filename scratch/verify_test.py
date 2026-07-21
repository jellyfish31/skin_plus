import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from db_helper import get_db_connection

connection = get_db_connection()
cursor = connection.cursor(dictionary=True)

cursor.execute("SELECT product_id, visual_signature, product_category FROM products WHERE product_id IN (1307490, 1307491)")
rows = cursor.fetchall()
for r in rows:
    print(f"ID: {r['product_id']} | Sig: {r['visual_signature']} | Cat: {r['product_category']}")

connection.close()
