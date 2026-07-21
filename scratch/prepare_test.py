import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from db_helper import get_db_connection

connection = get_db_connection()
cursor = connection.cursor()

# Clear existing test data
cursor.execute("DELETE FROM products WHERE visual_signature LIKE 'test_sig_%' OR visual_signature = 'new_test_sig_10g'")
connection.commit()

# Insert two mock products in the same group
cursor.execute("""
    INSERT INTO products (product_name, product_brand, product_category, product_store, product_price, visual_signature, created_at)
    VALUES ('Test Product A', 'Test Brand', 'Skincare', 'Watsons', 10.00, 'test_sig_10ml', NOW())
""")
id_a = cursor.lastrowid

cursor.execute("""
    INSERT INTO products (product_name, product_brand, product_category, product_store, product_price, visual_signature, created_at)
    VALUES ('Test Product B', 'Test Brand', 'Skincare', 'Guardian', 12.00, 'test_sig_10g', NOW())
""")
id_b = cursor.lastrowid

connection.commit()
connection.close()

print(f"Mock products inserted. ID A: {id_a}, ID B: {id_b}")
