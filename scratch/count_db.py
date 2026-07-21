import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from db_helper import get_db_connection

connection = get_db_connection()
cursor = connection.cursor()

cursor.execute("SHOW DATABASES")
databases = cursor.fetchall()
print("Databases:")
for db in databases:
    print(" -", db[0])

cursor.execute("USE skinplus_db")
cursor.execute("SHOW TABLES")
tables = cursor.fetchall()
print("\nTables in skinplus_db:")
for tb in tables:
    print(" -", tb[0])

connection.close()
