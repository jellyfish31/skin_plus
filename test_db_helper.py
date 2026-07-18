import pytest
import mysql.connector
from db_helper import get_db_connection, add_history_log

def test_get_db_connection():
    """Verify that get_db_connection successfully connects to MySQL and is not None."""
    db = get_db_connection()
    assert db is not None
    assert db.is_connected() is True
    db.close()

def test_add_history_log():
    """Verify that add_history_log successfully writes a log record and returns True."""
    db = get_db_connection()
    assert db.is_connected() is True
    
    # Insert a test log entry
    result = add_history_log(
        db,
        action_type='TEST_LOG',
        target_identifier='PyTest Unit Test',
        old_value='Before Test',
        new_value='After Test',
        admin_user='system'
    )
    assert result is True
    
    # Query database to confirm the log exists and delete it to clean up
    cursor = db.cursor()
    cursor.execute("SELECT log_id FROM history_logs WHERE action_type = 'TEST_LOG' AND target_identifier = 'PyTest Unit Test'")
    row = cursor.fetchone()
    assert row is not None
    log_id = row[0]
    
    # Delete the test log to keep history clean
    cursor.execute("DELETE FROM history_logs WHERE log_id = %s", (log_id,))
    db.commit()
    
    cursor.close()
    db.close()

if __name__ == "__main__":
    import pytest
    pytest.main([__file__])

