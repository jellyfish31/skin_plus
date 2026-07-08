import mysql.connector

from db_helper import get_db_connection
try:
    connection = get_db_connection()

    if connection.is_connected():
        print("Success! Python is connected to MySQL database.")
        
        # Displaying server info
        db_info = connection.get_server_info()
        print(f"MySQL Server version: {db_info}")

except Exception as e:
    print(f"Error: {e}")

finally:
    if 'connection' in locals() and connection.is_connected():
        connection.close()
        print("Connection closed.")