# archive_history.py
import mysql.connector

from db_helper import get_db_connection

def archive_old_prices():
    try:
        db = get_db_connection()
        cursor = db.cursor()
        print("📦 Connected to database. Running History Migration (Keeping Top 2 Per Item)...")

        # Create tracking table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS temp_latest_ids (latest_id INT PRIMARY KEY)
        """)
        cursor.execute("TRUNCATE TABLE temp_latest_ids")

        # Isolate the top 2 newest records per store item
        cursor.execute("""
            INSERT INTO temp_latest_ids (latest_id)
            SELECT product_id FROM (
                SELECT product_id,
                       @row_num := IF(@prev_store = product_store AND @prev_name = product_name, @row_num + 1, 1) AS row_num,
                       @prev_store := product_store,
                       @prev_name := product_name
                FROM products, (SELECT @row_num := 0, @prev_store := '', @prev_name := '') r
                WHERE visual_signature IS NOT NULL
                ORDER BY product_name, product_store, created_at DESC
            ) as ranked_products
            WHERE row_num <= 2
        """)

        # Move rank 3 and older records to historical tables
        cursor.execute("""
            INSERT INTO data_history (product_id, product_name, product_brand, product_category, product_price, product_store, product_image, visual_signature, created_at)
            SELECT p.product_id, p.product_name, p.product_brand, p.product_category, p.product_price, p.product_store, p.product_image, p.visual_signature, p.created_at
            FROM products p
            LEFT JOIN temp_latest_ids t ON p.product_id = t.latest_id
            WHERE p.visual_signature IS NOT NULL AND t.latest_id IS NULL
        """)

        # Delete old duplicate values from active table
        cursor.execute("""
            DELETE p FROM products p
            LEFT JOIN temp_latest_ids t ON p.product_id = t.latest_id
            WHERE p.visual_signature IS NOT NULL AND t.latest_id IS NULL
        """)

        # Drop temporary checklist tracking structure
        cursor.execute("DROP TABLE IF EXISTS temp_latest_ids")
        
        db.commit()
        print("✅ History migration completed successfully! Active table is fully streamlined.")

    except Exception as e:
        print(f"❌ Migration Error Encountered: {e}")
        db.rollback()
    finally:
        cursor.close()
        db.close()

if __name__ == "__main__":
    archive_old_prices()