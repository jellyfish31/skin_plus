import mysql.connector
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException
import time
import datetime
import traceback

# --- 1. CONFIG ---
brands = ["Skintific", "Cetaphil", "Garnier", "Cosrx", "Medicube", "Glad2Glow", "Eucerin", "Aiken"]

category_map = {
    "mask": "Mask",
    "clay": "Mask",            
    "sunscreen": "Sunscreen",
    "sunblock": "Sunscreen",
    "micellar": "Micellar Water",
    "cleansing water": "Micellar Water", 
    "biphase": "Micellar Water",         
    "cleans": "Cleanser",      
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

# --- 2. DRIVER SYSTEM INITIALIZATION ---
options = Options()
options.binary_location = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
options.add_argument('--start-maximized')
options.add_argument("--remote-allow-origins=*") 
options.add_experimental_option("excludeSwitches", ["enable-automation"])
options.add_experimental_option('useAutomationExtension', False)
options.add_argument("--disable-blink-features=AutomationControlled")

driver = webdriver.Chrome(options=options)
driver.execute_cdp_cmd('Page.addScriptToEvaluateOnNewDocument', {
    'source': 'Object.defineProperty(navigator, "webdriver", {get: () => undefined})'
})

# --- 3. DATABASE SETUP CONNECTION ---
from db_helper import get_db_connection
try:
    db = get_db_connection()
    cursor = db.cursor()
    print("✅ Connected to database")
except Exception as e:
    print(f"❌ DB Fail: {e}")
    driver.quit()
    exit()

# --- 4. THE WEB SCRAPING ENGINE (WITH ACCIDENT WRAPPING) ---
try:
    for brand in brands:
        for cat_search in ["Face Wash", "Toner", "Serum", "Moisturizer", "Sunscreen", "Mask", "Micellar Water"]:
            search_query = f"{brand} {cat_search}"
            print(f"\n🔎 Searching Watsons: {search_query}...")
            
            try:
                driver.get(f"https://www.watsons.com.my/search?text={search_query}")
                time.sleep(4) 
                
                # Smooth incremental scrolling down to reveal lazy-loaded items
                last_height = driver.execute_script("return document.body.scrollHeight")
                current_scroll_position = 0
                scroll_step = 700  
                
                while current_scroll_position < last_height:
                    current_scroll_position += scroll_step
                    driver.execute_script(f"window.scrollTo(0, {current_scroll_position});")
                    time.sleep(1.0)  
                    last_height = driver.execute_script("return document.body.scrollHeight")
                    if current_scroll_position > 12000:
                        break
                        
                time.sleep(1.5)
                
                # Dynamic Pagination / Load More Grid Canvas Expansion
                for pagination_loop in range(4): 
                    try:
                        # ✨ FIXED TYPO: Changed By.開X_PATH back to standard By.XPATH
                        load_more_btn = driver.find_elements(By.XPATH, "//button[contains(text(), 'Load More') or contains(text(), 'Show More') or @class='btn-load-more']")
                        if load_more_btn and load_more_btn[0].is_displayed():
                            driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", load_more_btn[0])
                            time.sleep(1)
                            driver.execute_script("arguments[0].click();", load_more_btn[0])
                            print("    🔄 [LOAD MORE TRIGGERED] Expanding grid canvas items...")
                            time.sleep(3) 
                        else:
                            break
                    except Exception:
                        break

                # ✨ UPGRADED GUARDRAIL: Multi-element target wait avoids continuous timeouts on missing items
                try:
                    WebDriverWait(driver, 10).until(
                        lambda d: d.find_elements(By.TAG_NAME, "e2-product-tile") or 
                                  d.find_elements(By.CLASS_NAME, "search-empty-title") or
                                  "no results" in d.page_source.lower()
                    )
                except TimeoutException:
                    print(f"    ⚠️ Network lag or page profile timeout. Skipping safely...")
                    continue

                product_cards = driver.find_elements(By.TAG_NAME, "e2-product-tile")
                
                if not product_cards:
                    print(f"    ⚠️ No products available on platform for search parameters.")
                    continue

                print(f"    📊 [Card Extraction Profile] Located {len(product_cards)} product cards.")

                for card in product_cards:
                    try:
                        name_el = card.find_elements(By.CSS_SELECTOR, "h2.productName a")
                        price_el = card.find_elements(By.CSS_SELECTOR, ".formatted-value")
                        img_el = card.find_elements(By.CSS_SELECTOR, "e2-product-thumbnail img")
                        
                        if not name_el or not price_el:
                            continue
                            
                        full_name = " ".join(name_el[0].text.split())
                        if not full_name: 
                            continue
                        
                        name_lower = full_name.lower()
                        if brand.lower() not in name_lower:
                            continue

                        skip_keywords = ["hand", "shampoo", "conditioner", "hair", "body milk", "heel", "concealer", "foundation", "baby"]
                        should_skip = any(word in name_lower for word in skip_keywords)
                        
                        if "body" in name_lower and "face" not in name_lower:
                            should_skip = True
                        if should_skip:
                            continue

                        matched_cat = next((formal_name for keyword, formal_name in category_map.items() if keyword in name_lower), None)

                        if matched_cat:
                            price_text = price_el[0].text.replace("RM", "").replace(",", "").strip()
                            if not price_text: 
                                continue
                            
                            if "\n" in price_text:
                                price_text = price_text.split("\n")[0]
                            price_val = float(price_text)

                            clean_name_check = name_lower.strip().replace("moisturiser", "moisturizer")

                            # --- EXACT MATCH PARSING RESTRUCTURE BLOCK ---
                            if clean_name_check == "skintific 5x ceramide low ph cleanser":
                                full_name = "Skintific 5X Ceramide Low pH Cleanser 120ML"
                                name_lower = full_name.lower()
                            elif clean_name_check == "skintific 5% aha/bha exfoliating toner 80 ml":
                                full_name = "Skintific Aha Bha Pha 5% Exfoliating Toner 80 ML"
                                name_lower = full_name.lower()
                            elif clean_name_check == "aiken niacinamide bright foam cleanser 100 g":
                                full_name = "Aiken Niacinamide Bright Anti Spot Brightening Facial Cleanser 100 G"
                                name_lower = full_name.lower()
                            elif clean_name_check == "aiken prebiotic facial cleanser 120 g":
                                full_name = "Aiken Prebiotic 8x Premium Biotics Facial Cleanser 120 G"
                                name_lower = full_name.lower()
                            elif clean_name_check == "glad2glow glycolic oil control facial wash 70 ml":
                                full_name = "Glad2glow Glycolic Facial Wash 70 G"
                                name_lower = full_name.lower()
                            elif clean_name_check == "aiken prebiotic m.emulsion oil cleanser 90 g":
                                full_name = "Aiken Prebiotic 8x Premium Biotics Micro Emulsion Oil Cleanser 90 G"
                                name_lower = full_name.lower()
                            elif clean_name_check == "aiken niacinamide bright toner 100 ml":
                                full_name = "Aiken Niacidamide Bright Brightening Glow Toner 100 ML"
                                name_lower = full_name.lower()

                            # --- PREFIX-MATCH PARSING RESTRUCTURE BLOCK ---
                            elif clean_name_check.startswith("aiken prebiotic spf25 moisturizer"):
                                size_suffix = full_name[len("aiken prebiotic spf25 moisturiser"):].strip()
                                full_name = f"Aiken Prebiotic 8X Premium Biotics Moisturiser Spf25 {size_suffix}".strip()
                                name_lower = full_name.lower()
                            elif clean_name_check.startswith("aiken prebiotic jelly moisturizer"):
                                size_suffix = full_name[len("aiken prebiotic jelly moisturiser"):].strip()
                                full_name = f"Aiken Prebiotic 8X Premium Biotics Water Jelly Moisturiser {size_suffix}".strip()
                                name_lower = full_name.lower()
                            elif clean_name_check.startswith("aiken prebiotic pudding glow moist"):
                                raw_cut_length = len("aiken prebiotic pudding glow moist")
                                size_suffix = full_name[raw_cut_length:].strip()
                                size_suffix = size_suffix.lower().replace("uriser", "").replace("urizer", "").strip().upper()
                                full_name = f"Aiken Prebiotic 8X Premium Biotics Pudding Glow Moisturiser {size_suffix}".strip()
                                name_lower = full_name.lower()
                            elif clean_name_check.startswith("aiken prebiotic moisturizer"):
                                size_suffix = full_name[len("aiken prebiotic moisturiser"):].strip()
                                full_name = f"Aiken Prebiotic 8X Premium Biotics Gel Moisturiser {size_suffix}".strip()
                                name_lower = full_name.lower()

                            tracking_identity = f"{name_lower}_{price_val}"
                            if tracking_identity in scraped_today:
                                continue

                            img_url = "no_image.png"
                            if img_el:
                                temp_img = img_el[0].get_attribute("src")
                                if temp_img:
                                    temp_img = temp_img.strip()
                                    if ("publishing" in temp_img or "prodcat" in temp_img) and "badge" not in temp_img.lower() and not temp_img.startswith("data:image"):
                                        img_url = temp_img

                            sql = """INSERT INTO products 
                                     (product_name, product_brand, product_price, product_store, product_category, product_image) 
                                     VALUES (%s, %s, %s, %s, %s, %s)"""
                            val = (full_name, brand, price_val, "Watsons", matched_cat, img_url)
                            
                            cursor.execute(sql, val)
                            db.commit() 
                            
                            scraped_today.add(tracking_identity)
                            total_added += 1
                            print(f"    \t✅ [ADDED TO DB] {matched_cat}: {full_name} (RM {price_val})")
                    
                    except Exception:
                        continue

            except Exception as search_err:
                print("    ❌ Watsons Search Execution Fault Encountered")
                traceback.print_exc()

finally:
    # 🧹 SYSTEM CLEANUP BACKUP GUARD: Guarantees background processes clear even on manual crash aborts
    print("\n" + "="*40)
    print(f" 📦 Total Unique Watsons Products Added Today: {total_added}")
    print("="*40)
    
    if 'db' in locals() and db.is_connected():
        cursor.close()
        db.close()
        print("🔌 Database connection closed cleanly.")
        
    if 'driver' in locals():
        print("🖥️ Shutting down Chrome Driver framework session...")
        driver.quit()