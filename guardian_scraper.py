import mysql.connector
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

# --- 1. CONFIGURATION ---
brands = ["Skintific", "Cetaphil", "Garnier", "Cosrx", "Medicube", "Glad2Glow", "Eucerin", "Aiken"]

category_map = {
    "mask": "Mask",
    "clay": "Mask",
    "sunscreen": "Sunscreen",
    "sunblock": "Sunscreen",
    "micellar": "Micellar Water",
    "cleansing water": "Micellar Water",
    "biphase": "Micellar Water",
    "cleanser": "Cleanser",
    "wash": "Cleanser",
    "toner": "Toner",
    "serum": "Serum",
    "eye": "Eye Care",
    "moisturizer": "Moisturizer",
    "moisturiser": "Moisturizer",
    "mosituriser": "Moisturizer", 
    "gel cream": "Moisturizer",
    "jelly cream": "Moisturizer",   
    "cream": "Moisturizer",         
    "lotion": "Moisturizer"         
}

total_added = 0
scraped_today = set()

options = webdriver.ChromeOptions()
options.add_argument('--start-maximized')
options.add_argument("user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36")

options.add_experimental_option("excludeSwitches", ["enable-automation"])
options.add_experimental_option('useAutomationExtension', False)
options.add_argument("--disable-blink-features=AutomationControlled")

driver = webdriver.Chrome(options=options)

driver.execute_cdp_cmd('Page.addScriptToEvaluateOnNewDocument', {
    'source': 'Object.defineProperty(navigator, "webdriver", {get: () => undefined})'
})

# --- 2. DATABASE CONNECTION ---
from db_helper import get_db_connection, add_history_log
import sys
try:
    db = get_db_connection()
    cursor = db.cursor()
    print("✅ Connected to database")
    add_history_log(db, 'SCRAPE_START', 'Guardian Scraper', 'Idle', 'Scraping started')
except Exception as e:
    print(f"❌ Database connection failed: {e}")
    driver.quit()
    exit()

# --- 3. THE BRAND SEARCH ENGINE ---
try:
    for brand in brands:
    print(f"\n🚀 [GUARDIAN] Searching Brand: {brand}")
    last_page_items = [] 
    
    for page_num in range(1, 3): 
        url = f"https://www.guardian.com.my/search.html?query={brand}&page={page_num}"
        
        try:
            driver.get(url)
            time.sleep(6) 
            
            WebDriverWait(driver, 15).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, ".item-h2style-2bH span"))
            )
        except Exception:
            if page_num == 1:
                print(f"   ⚠️ No products found for {brand}.")
            break 

        # IMAGE-STABILIZING PROGRESSIVE SCROLL ENGINE
        total_height = driver.execute_script("return document.body.scrollHeight")
        current_pos = 0
        step_increment = 500
        
        while current_pos < total_height:
            current_pos += step_increment
            driver.execute_script(f"window.scrollTo(0, {current_pos});")
            time.sleep(1.2) 
            total_height = driver.execute_script("return document.body.scrollHeight")
            if current_pos > 8000: 
                break
                
        time.sleep(2) 

        current_names_elements = driver.find_elements(By.CSS_SELECTOR, ".item-h2style-2bH span")
        current_prices_elements = driver.find_elements(By.CSS_SELECTOR, ".price-highlightedPrice-34K")
        current_image_elements = driver.find_elements(By.CSS_SELECTOR, ".item-image-sxd")

        # --- DUPLICATE PAGE CHECK ---
        current_page_item_names = [item.text for item in current_names_elements]
        if current_page_item_names == last_page_items:
            print(f"   🛑 Page {page_num} is a duplicate of Page {page_num-1}. Skipping...")
            break
        last_page_items = current_page_item_names 

        # --- PROCESSING PRODUCTS ---
        for i in range(len(current_names_elements)):
            if i >= len(current_names_elements) or i >= len(current_prices_elements):
                continue
                
            try:
                full_name = current_names_elements[i].text.strip()
                if not full_name: 
                    continue
                
                name_lower = full_name.lower()
                
                # ✨ UNIFY CODES TO BASE TEXT IMMEDIATELY
                name_lower = name_lower.replace("tto", "tea tree oil").replace("t3", "tea tree")
                
                if brand.lower() not in name_lower:
                    continue 

                skip_keywords = ["body", "hand", "shampoo", "conditioner", "hair", "body milk", "heel", "concealer", "foundation", "baby"]
                should_skip = False
                for skip_word in skip_keywords:
                    if skip_word in name_lower:
                        should_skip = True
                        break
                
                if "body" in name_lower and "face" not in name_lower:
                    should_skip = True
                
                if should_skip:
                    print(f"   🚫 [SKIPPED BODY/HAIR ITEM] {full_name}")
                    continue

                # CATEGORY CHECK & MAPPING
                matched_cat = None
                for keyword, formal_name in category_map.items():
                    if keyword in name_lower:
                        matched_cat = formal_name
                        break

                if matched_cat:
                    price_text = current_prices_elements[i].text.replace("MYR", "").replace("RM", "").strip()
                    if not price_text: 
                        continue
                    
                    try:
                        if "\n" in price_text:
                            price_text = price_text.split("\n")[0]
                        price_val = float(price_text)
                    except ValueError:
                        continue

                    # Clean string representation for exact matching guardrails
                    clean_name_check = name_lower.strip()

                    # ─── ✨ GUARDIAN NAME NORMALIZATION HANDLERS ───
                    if clean_name_check == "glad2glow yuzu aha blackhead exfoliating cleanser":
                        full_name = "Glad2glow Yuzu Aha Blackhead Exfoliating Cleanser 70 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [VOLUME PATCH DEPLOYED] Appended size tag: {full_name}")

                    elif clean_name_check == "skintific 5x ceramide cleanser 120 ml":
                        full_name = "Skintific 5x Ceramide Low Ph Cleanser 120 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [SKINTIFIC RE-NAME DEPLOYED] Transformed title: {full_name}")

                    elif clean_name_check == "skintific niacinamide cleanser 120 ml":
                        full_name = "Skintific Niacinamide Brightening Facial Cleanser 120 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [SKINTIFIC RE-NAME DEPLOYED] Transformed title: {full_name}")

                    elif clean_name_check == "glad2glow ceramide low ph blueberry gel cleanser 70 ml":
                        full_name = "Glad2glow Blueberry Ceramide Low Ph Gel Cleanser 70 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [GLAD2GLOW RE-NAME DEPLOYED] Transformed title: {full_name}")
                    
                    elif clean_name_check == "cetaphil skin cleanser 473 ml":
                        full_name = "Cetaphil Gentle Skin Cleanser For Face & Body 473 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [CETAPHIL RE-NAME DEPLOYED] Transformed title: {full_name}")

                    elif clean_name_check == "cetaphil gentle skin cleanser 236 ml":
                        full_name = "Cetaphil Gentle Skin Cleanser For Face & Body 236 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [CETAPHIL RE-NAME DEPLOYED] Transformed title: {full_name}")

                    # ✨ FIXED STRING CONTEXT: Changed from "tto" to matched "tea tree oil" words
                    elif clean_name_check == "aiken tea tree oil facial cleanser+makeup remover 150 ml":
                        full_name = "Aiken Tea Tree Oil Facial Cleanser& Makeup Remover 150 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [AIKEN RE-NAME DEPLOYED] Transformed title: {full_name}")

                    elif clean_name_check == "aiken tea tree oil toner (r) 100 ml":
                        full_name = "Aiken Tea Tree Oil Toner 100 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [AIKEN RE-NAME DEPLOYED] Transformed title: {full_name}")

                    elif clean_name_check == "aiken tea tree oil moisturiser (r) 75 ml":
                        full_name = "Aiken Tea Tree Oil Moisturiser 75 ML"
                        name_lower = full_name.lower()
                        print(f"    \t🛠️ [AIKEN RE-NAME DEPLOYED] Transformed title: {full_name}")

                    tracking_identity = f"{name_lower}_{price_val}"
                    if tracking_identity in scraped_today:
                        continue

                    # ─── ✨ REINFORCED LAZY-LOAD IMAGE PROPERTY FALLBACKS ───
                    img_url = ""
                    if i < len(current_image_elements):
                        img_url = current_image_elements[i].get_attribute("data-src")
                        
                        if not img_url or "placeholder" in img_url or img_url.startswith("data:image") or "blank.png" in img_url or "blank.gif" in img_url:
                            img_url = current_image_elements[i].get_attribute("src")
                        
                        if not img_url or "placeholder" in img_url or img_url.startswith("data:image") or "blank.png" in img_url or "blank.gif" in img_url:
                            img_url = current_image_elements[i].get_attribute("ng-img")
                            
                        if not img_url or "placeholder" in img_url or img_url.startswith("data:image") or "blank.png" in img_url or "blank.gif" in img_url:
                            img_url = current_image_elements[i].get_attribute("data-original")

                    if img_url and img_url.startswith("/"):
                        if not img_url.startswith("//"):
                            img_url = "https://www.guardian.com.my" + img_url
                        else:
                            img_url = "https:" + img_url

                    if not img_url or "placeholder" in img_url or img_url.startswith("data:image") or "blank.png" in img_url or "blank.gif" in img_url:
                        img_url = "no_image.png"

                    sql = """INSERT INTO products 
                             (product_name, product_brand, product_price, product_store, product_category, product_image) 
                             VALUES (%s, %s, %s, %s, %s, %s)"""
                    val = (full_name, brand, price_val, "Guardian", matched_cat, img_url)
                    
                    cursor.execute(sql, val)
                    db.commit() 
                    
                    scraped_today.add(tracking_identity)
                    total_added += 1
                    print(f"   ✅ [P{page_num}] Added {matched_cat}: {full_name} (RM {price_val})")
                
            except Exception as item_error:
                continue 

except Exception as e:
    print(f"❌ Error during execution: {e}")
finally:
    # --- 4. FINAL SUMMARY DASHBOARD ---
    print("\n" + "="*50)
    print(f"🏁 GUARDIAN SCRAPE COMPLETE!")
    print(f"📦 Total Products Cleaned & Processed: {total_added}")
    print("="*50)

    if 'db' in locals():
        try:
            exc_type, exc_value, tb = sys.exc_info()
            if exc_type is not None and exc_type is not SystemExit:
                error_msg = f"Failed: {str(exc_value)[:200]}"
                add_history_log(db, 'SCRAPE_FAILED', 'Guardian Scraper', 'Scraping', error_msg)
            else:
                add_history_log(db, 'SCRAPE_COMPLETE', 'Guardian Scraper', 'Scraping', f'Successfully added {total_added} products')
        except Exception as log_err:
            print(f"Error logging exit: {log_err}")
            
        cursor.close()
        db.close()
        print("🔌 Database connection closed cleanly.")
        
    if 'driver' in locals():
        driver.quit()