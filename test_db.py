import mysql.connector

try:
    # Connecting to XAMPP MySQL
    connection = mysql.connector.connect(
        host="localhost",
        user="root",        # XAMPP default
        password="",        # XAMPP default is empty
        database="skinplus_db"
    )

    if connection.is_connected():
        print("✅ Success! Python is connected to XAMPP MySQL.")
        
        # Displaying server info
        db_info = connection.get_server_info()
        print(f"MySQL Server version: {db_info}")

except Exception as e:
    print(f"❌ Error: {e}")

finally:
    if 'connection' in locals() and connection.is_connected():
        connection.close()
        print("Connection closed.")