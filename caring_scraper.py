import mysql.connector
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
import time
import urllib.parse
import traceback

brands = ["Skintific", "Cetaphil", "Garnier", "Cosrx"]

category_map = {
    "cleanser": "Cleanser", 
    "wash": "Cleanser", 
    "toner": "Toner", 
    "serum": "Serum", 
    "sunscreen": "Sunscreen",
    "sunblock": "Sunscreen", 
    "mask": "Mask", 
    "micellar": "Micellar Water", 
    "eye": "Other",
    "moisturizer": "Moisturizer", 
    "moisturiser": "Moisturizer", 
    "mosituriser": "Moisturizer", 
    "moisture": "Moisturizer",
    "gel cream": "Moisturizer", 
    "jelly cream": "Moisturizer", 
    "cream": "Moisturizer", 
    "lotion": "Moisturizer", 
    "spf": "Sunscreen",        
}

total_added = 0
scraped_today = set()

# intialize chrome
options = Options()
options.binary_location = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

options.add_argument('--start-maximized')
options.add_argument("--remote-allow-origins=*")

# halang detection from Cloudflare
options.add_argument("--disable-quic") 

driver = webdriver.Chrome(options=options)

driver.execute_cdp_cmd('Page.addScriptToEvaluateOnNewDocument', {
    'source': 'Object.defineProperty(navigator, "webdriver", {get: () => undefined})'
})

# connect to db
from db_helper import get_db_connection, add_history_log
import sys
try:
    db = get_db_connection()
    cursor = db.cursor()
    print("Connected to database")
    add_history_log(db, 'SCRAPE_START', 'CARiNG Pharmacy Scraper', 'Idle', 'Scraping started')
except Exception as e:
    print(f"DB Fail: {e}")
    driver.quit()
    exit()

# searching and scraping logik
try:
    for brand in brands:
        for cat_search in ["Face Wash", "Toner", "Serum", "Gel", "Moisturizer", "Sunscreen", "Mask", "Micellar Water"]:
            search_query = f"{brand} {cat_search}"
            print(f"\n🔎 Searching CARiNG Pharmacy: {search_query}...")

            try:
                # make space jadi (+) / %20
                encoded_query = urllib.parse.quote_plus(search_query)

                driver.get(f"https://estore.caring2u.com/search?q={encoded_query}&rows=24&start=0")
                time.sleep(6)

                total_height = driver.execute_script("return document.body.scrollHeight")
                current_pos = 0
                step_increment = 600

                # halang lazy loading 
                while current_pos < total_height:
                    current_pos += step_increment
                    driver.execute_script(f"window.scrollTo(0, {current_pos});")
                    time.sleep(1.2)
                    total_height = driver.execute_script("return document.body.scrollHeight")
                    if current_pos > 10000:
                        break

                time.sleep(2)

                try:
                    product_cards = driver.find_elements(By.CSS_SELECTOR, "div[data-product-grid], .yv-product-card")
                    print(f"[Card Extraction Profile] Total Parent Cards Located: {len(product_cards)}")

                    for card in product_cards:
                        try:                           
                            name_el = card.find_elements(By.CSS_SELECTOR, ".yv-product-title, a[href*='/products/']")
                            price_el = card.find_elements(By.CSS_SELECTOR, ".yv-product-price, .price, span[class*='price']")
                            img_el = card.find_elements(By.CSS_SELECTOR, ".image-wrapper img.product-first-img")

                            if not name_el or not price_el:
                                continue

                            full_name = name_el[0].text.strip()
                            if not full_name:
                                full_name = name_el[0].get_attribute("title") or name_el[0].get_attribute("aria-label")

                            if not full_name:
                                continue

                            name_lower = full_name.lower()

                            # skip if brand tiada pada nama produk
                            if brand.lower() not in name_lower:
                                continue

                            skip_keywords = ["body", "hand", "shampoo", "conditioner", "hair", "body milk", "heel", "concealer", "foundation", "baby"]
                            should_skip = any(skip_word in name_lower for skip_word in skip_keywords)

                            if "body" in name_lower and "face" not in name_lower:
                                should_skip = True
                            if should_skip:
                                continue

                            # check if any category_map ada dalam nama produk
                            matched_cat = next((formal_name for keyword, formal_name in category_map.items() if keyword in name_lower), None)

                            if matched_cat:
                                price_text = price_el[0].text.replace("RM", "").replace("MYR", "").strip()
                                if not price_text:
                                    continue

                                if "\n" in price_text:
                                    price_text = price_text.split("\n")[0]

                                try:
                                    price_val = float(price_text)
                                except ValueError:
                                    continue

                                # clean name
                                clean_name_check = name_lower.strip().replace("moisturiser", "moisturizer")

                                # pembetulan nama produk
                                if clean_name_check.startswith("aiken prebiotic spf25 moisturizer"):
                                    size_suffix = full_name[len("aiken prebiotic spf25 moisturiser"):].strip()
                                    full_name = f"Aiken Prebiotic 8X Premium Biotics Moisturiser Spf25 {size_suffix}".strip()
                                    name_lower = full_name.lower()
                                    print(f"    \t[AIKEN SPF MOISTURIZER RE-NAME] Transformed title: {full_name}")

                                elif clean_name_check.startswith("aiken prebiotic jelly moisturizer"):
                                    size_suffix = full_name[len("aiken prebiotic jelly moisturiser"):].strip()
                                    full_name = f"Aiken Prebiotic 8X Premium Biotics Water Jelly Moisturiser {size_suffix}".strip()
                                    name_lower = full_name.lower()
                                    print(f"    \t[AIKEN JELLY MOISTURIZER RE-NAME] Transformed title: {full_name}")

                                elif clean_name_check.startswith("aiken prebiotic pudding glow moist"):
                                    raw_cut_length = len("aiken prebiotic pudding glow moist")
                                    size_suffix = full_name[raw_cut_length:].strip()
                                    size_suffix = size_suffix.lower().replace("uriser", "").replace("urizer", "").strip().upper()
                                    full_name = f"Aiken Prebiotic 8X Premium Biotics Pudding Glow Moisturiser {size_suffix}".strip()
                                    name_lower = full_name.lower()
                                    print(f"    \t[AIKEN PUDDING MOISTURIZER RE-NAME] Transformed title: {full_name}")

                                elif clean_name_check.startswith("aiken prebiotic moisturizer"):
                                    size_suffix = full_name[len("aiken prebiotic moisturiser"):].strip()
                                    full_name = f"Aiken Prebiotic 8X Premium Biotics Gel Moisturiser {size_suffix}".strip()
                                    name_lower = full_name.lower()
                                    print(f"    \t[AIKEN GEL MOISTURIZER RE-NAME] Transformed title: {full_name}")

                                tracking_identity = f"{name_lower}_{price_val}"
                                if tracking_identity in scraped_today:
                                    continue

                                # image scraping logik
                                img_url = ""
                                if img_el:
                                    img_target = img_el[0]
                                    img_url = img_target.get_attribute("data-original")

                                    if not img_url or "placeholder" in img_url or img_url.startswith("data:image"):
                                        raw_srcset = img_target.get_attribute("srcset") or img_target.get_attribute("data-srcset")
                                        if raw_srcset and raw_srcset.strip():
                                            img_url = raw_srcset.split(",")[0].strip().split(" ")[0].strip()

                                    if not img_url or "placeholder" in img_url or img_url.startswith("data:image"):
                                        img_url = img_target.get_attribute("src")

                                # URL 
                                if img_url:
                                    img_url = img_url.strip()
                                    if img_url.startswith("//"):
                                        img_url = "https:" + img_url
                                    elif img_url.startswith("/"):
                                        img_url = "https://estore.caring2u.com" + img_url

                                if not img_url or "placeholder" in img_url or img_url.startswith("data:image") or "blank.png" in img_url:
                                    img_url = "no_image.png"

                                # push data to db
                                sql = """INSERT INTO products 
                                         (product_name, product_brand, product_price, product_store, product_category, product_image) 
                                         VALUES (%s, %s, %s, %s, %s, %s)"""
                                val = (full_name, brand, price_val, "CARiNG Pharmacy", matched_cat, img_url)

                                cursor.execute(sql, val)
                                db.commit()

                                scraped_today.add(tracking_identity)
                                total_added += 1
                                print(f"[ADDED TO DB] {matched_cat}: {full_name[:45]}... (RM {price_val})")

                        except Exception:
                            continue
                except Exception as e:
                    print(f"Grid Processing Fault: {str(e)[:60]}")

            except Exception as search_err:
                print("Network Request Timeout Encountered (Skipping query block)")

            time.sleep(1.5)

except Exception as e:
    print(f"Error during execution: {e}")
finally:
    print("\n" + "="*40)
    print(f"Total Unique CARiNG Pharmacy Products Added: {total_added}")
    print(f"Status: ENGINE SYNCHRONIZATION RUN COMPLETE")
    print("="*40)

    if 'db' in locals():
        try:
            exc_type, exc_value, tb = sys.exc_info()
            if exc_type is not None and exc_type is not SystemExit:
                error_msg = f"Failed: {str(exc_value)[:200]}"
                add_history_log(db, 'SCRAPE_FAILED', 'CARiNG Pharmacy Scraper', 'Scraping', error_msg)
            else:
                add_history_log(db, 'SCRAPE_COMPLETE', 'CARiNG Pharmacy Scraper', 'Scraping', f'Successfully added {total_added} products')
        except Exception as log_err:
            print(f"Error logging exit: {log_err}")
            
        cursor.close()
        db.close()
        print(" Database connection closed cleanly.")
        
    if 'driver' in locals():
        driver.quit()