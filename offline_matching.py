import mysql.connector
import csv
import os

from db_helper import get_db_connection, add_history_log
try:
    db = get_db_connection()
    cursor = db.cursor()
    print("Connected to database")
    add_history_log(db, 'MATCHING_START', 'Offline Signature Matching', 'Unassigned Signatures', 'Matching started')
except Exception as e:
    print(f"Database Connection Failed: {e}")
    exit()

csv_filename = 'visual_signature_list.csv'

if not os.path.exists(csv_filename):
    print(f"Error: Cannot find '{csv_filename}' in this directory.")
    db.close()
    exit()

print("Starting case-insensitive sync loop for NULL signatures only...")

try:

    with open(csv_filename, mode='r', encoding='utf-8') as file:
        reader = csv.reader(file)
        headers = next(reader)
        
        try:
            name_idx = headers.index('product_name')
            sig_idx = headers.index('visual_signature')
        except ValueError:
            print("Error: CSV must have headers labeled exactly 'product_name' and 'visual_signature'.")
            db.close()
            exit()

        aligned_records = 0
        skipped_records = 0

        print("Matching clean visual signatures to unassigned items...")
        for row_data in reader:
            if len(row_data) <= max(name_idx, sig_idx):
                continue

            product_name = row_data[name_idx].strip().lower()
            master_signature = row_data[sig_idx].strip().lower()

            if not product_name or not master_signature or master_signature == 'pending_admin':
                skipped_records += 1
                continue

            update_sql = """
                UPDATE products 
                SET visual_signature = %s 
                WHERE LOWER(product_name) = %s 
                  AND visual_signature IS NULL
                  AND product_id IN (
                      SELECT product_id FROM (
                          SELECT product_id FROM products 
                          ORDER BY product_id DESC 
                          LIMIT 2000
                      ) AS recent_items
                  )
            """
            cursor.execute(update_sql, (master_signature, product_name))
            aligned_records += cursor.rowcount


        db.commit()

    print("\nProcessing Complete!")
    print(f"• {aligned_records} new row profiles successfully assigned their missing signatures.")
    print(f"• {skipped_records} rows skipped from CSV (blank or pending).")


    cursor.execute("SELECT COUNT(*) FROM products WHERE visual_signature IS NULL")
    unmapped_count = cursor.fetchone()[0]
    
    if unmapped_count > 0:
        print(f"\nNote: There are still {unmapped_count} unassigned items left in the database with no signature.")
    else:
        print("\nExcellent! Every item inside your database now has a visual signature mapping.")

    add_history_log(db, 'MATCHING_COMPLETE', 'Offline Signature Matching', 'Matching', f'Successfully assigned {aligned_records} signatures')

except Exception as dbe:
    print(f"SQL Update Failed: {dbe}")
    if db is not None:
        add_history_log(db, 'MATCHING_FAILED', 'Offline Signature Matching', 'Matching', f'Failed: {str(dbe)[:200]}')
        db.rollback()

finally:
    if db is not None:
        cursor.close()
        db.close()