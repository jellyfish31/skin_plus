
import subprocess
import sys
import time

def execute_worker_script(script_name):
    """Executes an external script module safely and pipes real-time text output."""
    print(f"\n=========================================")
    print(f"🚀 STARTING WORKER TASK: {script_name}")
    print(f"=========================================")
    
    start_time = time.time()
    

    process = subprocess.run([sys.executable, script_name], capture_output=False)
    
    elapsed = time.time() - start_time
    
    if process.returncode == 0:
        print(f"🎯 SUCCESS: {script_name} finished smoothly in {elapsed:.2f} seconds.")
        return True
    else:
        print(f"❌ CRITICAL FAILURE: {script_name} stopped with errors. Halting pipeline execution.")
        return False

def main():
    print("🌟 SKIN+ Automated Processing Pipeline Framework Initialized 🌟")
    pipeline_start = time.time()



    if not execute_worker_script("caring_scraper.py"):
        sys.exit(1)

    time.sleep(120)

    if not execute_worker_script("caring_scraper2.py"):
        sys.exit(1)

    time.sleep(60)

    if not execute_worker_script("watsons_scraper.py"):
        sys.exit(1)

    time.sleep(60)

    if not execute_worker_script("guardian_scraper.py"):
        sys.exit(1)



    if not execute_worker_script("offline_matching.py"):
        sys.exit(1)


    if not execute_worker_script("sync_to_live.py"):
        sys.exit(1)

    total_pipeline_time = time.time() - pipeline_start
    print("\n🎉=========================================")
    print(f"  ALL SYSTEMS COMPLETED SUCCESSFULLY!")
    print(f"  Total Execution Runtime: {total_pipeline_time:.2f} seconds.")
    print("=========================================🎉")

if __name__ == "__main__":
    main()