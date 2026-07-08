<?php
// controllers/ApiController.php

require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Admin.php';

class ApiController {
    /**
     * Retrieves AI description for a product using Gemini.
     */
    public function getAiDescription() {
        header('Content-Type: application/json');

        $product_name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if (empty($product_name)) {
            echo json_encode(['success' => false, 'error' => 'Missing product name context']);
            exit();
        }

        // Gemini API Key credentials
        $keys = file_exists(__DIR__ . '/../config/Keys.php') ? include __DIR__ . '/../config/Keys.php' : [];
        $api_key = $keys['gemini_key_1'] ?? ''; 
        $endpointUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;

        $prompt = "You are an expert, fun, and highly engaging skincare guru chatbot for the SKIN+ platform.\n"
                . "Analyze this product name: '$product_name'.\n"
                . "Generate a crisp, objective analysis. You MUST respond ONLY with a raw JSON object containing these exact 4 keys:\n"
                . "1. 'skin_type': What skin types suit this item + an emoji (max 6 words).\n"
                . "2. 'apply_time': Exactly when to apply it in a routine + an emoji (max 7 words).\n"
                . "3. 'benefits': 1-2 engaging sentences highlighting what it does for the skin.\n"
                . "4. 'ingredients': 3 to 4 star ingredients separated by commas.\n\n"
                . "Do not include any markdown backticks, markdown code blocks like ```json, or extra prose text. Return ONLY the raw valid JSON string.";

        $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // High-fidelity fallbacks
        $output = [
            'success' => true,
            'skin_type' => 'All Skin Types ✨',
            'apply_time' => 'Morning & Night Routine ☀️🌙',
            'benefits' => 'Deeply hydrates, balances skin parameters, and seals in defensive hydration.',
            'ingredients' => 'Hyaluronic Acid, Ceramides, Glycerin'
        ];

        if (!$curl_error && $response) {
            $result_json = json_decode($response, true);
            if (isset($result_json['candidates'][0]['content']['parts'][0]['text'])) {
                $ai_raw_text = preg_replace('/^```json|```$/', '', trim($result_json['candidates'][0]['content']['parts'][0]['text']));
                $ai_json = json_decode(trim($ai_raw_text), true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($ai_json)) {
                    $output = array_merge(['success' => true], $ai_json);
                }
            }
        }

        echo json_encode($output);
        exit();
    }

    /**
     * Retrieves AI detailed breakdown with caching mechanism.
     */
    public function getAiDetailedBreakdown() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');

        $product_name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $visual_signature = isset($_POST['visual_signature']) ? trim($_POST['visual_signature']) : '';

        if (empty($product_name)) { 
            echo json_encode(['success' => false, 'error' => 'Missing product name context']);
            exit();
        }

        // Cache Check
        if (!empty($visual_signature)) {
            $cached = Product::getCachedAiDetailedBreakdown($visual_signature);
            if ($cached) {
                $output = [
                    'success'          => true,
                    'cached'           => true,
                    'skin_concerns'    => $cached['skin_type'],
                    'texture_feel'     => $cached['benefits'],
                    'routine_layering' => $cached['apply_time'],
                    'precautions'      => $cached['ingredients']
                ];
                
                usleep(750000); // Fluid loader delay
                echo json_encode($output);
                exit();
            }
        }

        // Cache Miss -> call Gemini API
        $keys = file_exists(__DIR__ . '/../config/Keys.php') ? include __DIR__ . '/../config/Keys.php' : [];
        $apiKey = $keys['gemini_key_2'] ?? '';
        $endpointUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        $prompt = "You are an elite cosmetic chemist, skincare educator, and luxury beauty advisor for the SKIN+ platform.\n"
                . "Analyze this skincare product:\n"
                . "'$product_name'\n\n"
                . "Your writing style should feel like a premium skincare magazine—modern, elegant, warm, and effortlessly easy to read.\n\n"
                . "Every section should:\n"
                . "• Be visually pleasing with generous spacing.\n"
                . "• Use tasteful emojis to guide the eye (never overuse them).\n"
                . "• Use short, clear sentences.\n"
                . "• Sound luxurious yet informative.\n"
                . "• Be beginner-friendly while remaining scientifically accurate.\n"
                . "• Avoid marketing hype or exaggerated claims.\n"
                . "• Never use HTML tags, Markdown, numbered lists, or bullet tags.\n\n"
                . "━━━━━━━━━━━━━━━━━━\n"
                . "Return ONLY a raw JSON object with these EXACT keys:\n"
                . "{\n"
                . "\"skin_concerns\": \"...\",\n"
                . "\"texture_feel\": \"...\",\n"
                . "\"routine_layering\": \"...\",\n"
                . "\"precautions\": \"...\"\n"
                . "}\n"
                . "━━━━━━━━━━━━━━━━━━\n\n"
                . "Formatting Requirements:\n\n"
                . "\"skin_concerns\"\n"
                . "Display each concern on its own line using a unique emoji.\n"
                . "Example style:\n"
                . "💧 Intense dehydration and dryness\n"
                . "🛡️ Compromised skin barrier defense\n\n"
                . "\"texture_feel\"\n"
                . "Describe the physical product characteristics in a single brief paragraph (max 3 sentences).\n"
                . "Example style:\n"
                . "A beautifully lightweight, milky emulsion that glides effortlessly onto the skin. It absorbs within seconds, leaving behind a velvety, non-greasy finish and a subtle cooling sensation.\n\n"
                . "\"routine_layering\"\n"
                . "Step-by-step usage rules separated strictly by line breaks.\n"
                . "Example style:\n"
                . "☀️ Start your morning routine by cleansing\n"
                . "💧 Apply two pumps of this serum to damp skin\n"
                . "🔒 Seal in with your favorite moisturizer and SPF\n\n"
                . "\"precautions\"\n"
                . "Points of warning or instructions separated strictly by line breaks.\n"
                . "Example style:\n"
                . "🧪 Always patch test behind the ear before first use\n"
                . "👁️ Keep clear of the delicate eye contour region\n"
                . "☀️ Ensure daily broad-spectrum sun defense during use\n\n"
                . "Do not wrap your output in markdown formatting tags like ```json. Return ONLY a valid JSON string.";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            echo json_encode(['success' => false, 'error' => 'API returned status code ' . $http_code]);
            exit();
        }

        $res_data = json_decode($response, true);
        $ai_raw_text = $res_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean out markdown blocks
        $cleaned_json = preg_replace('/^```json|```$/', '', trim($ai_raw_text));
        $parsed = json_decode(trim($cleaned_json), true);

        if (json_last_error() === JSON_ERROR_NONE && !empty($parsed)) {
            // Write to database cache
            if (!empty($visual_signature)) {
                Product::cacheAiDetailedBreakdown(
                    $visual_signature,
                    $parsed['skin_concerns'],
                    $parsed['routine_layering'],
                    $parsed['texture_feel'],
                    $parsed['precautions']
                );
            }

            echo json_encode([
                'success'          => true,
                'cached'           => false,
                'skin_concerns'    => $parsed['skin_concerns'],
                'texture_feel'     => $parsed['texture_feel'],
                'routine_layering' => $parsed['routine_layering'],
                'precautions'      => $parsed['precautions']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Unable to parse AI response. Raw output: ' . $ai_raw_text]);
        }
        exit();
    }

    /**
     * Resource-heavy vision matcher processing pending signature alignments.
     */
    public function aiImageMatcher() {
        Admin::initSession();
        if (!Admin::isLoggedIn()) {
            header("HTTP/1.1 403 Forbidden");
            echo "❌ Unauthorized access.";
            exit();
        }

        $db = Database::getMysqli();

        // Fetch up to 10 pending items
        $pending = $db->query("SELECT product_id, product_name, product_brand, product_category, product_image 
                                 FROM products WHERE visual_signature = 'PENDING_ADMIN' LIMIT 10");

        $processed_count = 0;

        if ($pending && $pending->num_rows > 0) {
            while ($item = $pending->fetch_assoc()) {
                $pid = $item['product_id'];
                $brand = $item['product_brand'];
                $category = $item['product_category'];
                $new_img_url = $item['product_image'];

                // 1. STAGE ONE: Pre-filter database by size metric numbers (ml / g)
                preg_match('/(\d+)\s*(ml|g)/i', $item['product_name'], $size_match);
                $size_query = "";
                if (!empty($size_match[1])) {
                    $numerical_size = $size_match[1];
                    $size_query = " AND (product_name LIKE '%$numerical_size%g%' OR product_name LIKE '%$numerical_size%ml%')";
                }

                // Fetch candidates
                $candidates = Product::fetchVisionCandidates($brand, $category, $size_query);

                if (empty($candidates)) {
                    continue;
                }

                // 2. STAGE TWO: Call Vision API
                $matched_signature = $this->callVisionAPI($new_img_url, $candidates);

                if ($matched_signature && $matched_signature !== 'NO_MATCH') {
                    Product::updateAiMatchedSignature($item['product_name'], $matched_signature);
                    $processed_count++;
                }
            }
        }

        echo "Success: Processed " . $processed_count . " items matching existing signatures successfully.";
        exit();
    }

    /**
     * Processes search-by-image camera frames or file uploads.
     */
    public function imageSearchProcessor() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['image_data'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid data stream packet access criteria fields.']);
            exit();
        }

        try {
            $rawBase64Payload = $_POST['image_data'];
            
            if (preg_match('/^data:image\/(\w+);base64,/', $rawBase64Payload, $matches)) {
                $rawBase64Payload = substr($rawBase64Payload, strpos($rawBase64Payload, ',') + 1);
            }
            
            $cleanBase64Data = trim($rawBase64Payload);

            $promptInstructions = "Identify the exact brand name and product title from this skincare item image by reading the printed label text. IGNORE THE PHYSICAL BOTTLE COLOR OR CORAL PACKAGING DESIGN (e.g., if it says Toner, focus strictly on text matching and ignore face wash formulations of similar color). Return ONLY the plain text brand and product name (e.g., 'Skintific Niacinamide Brightening Essence Toner'). Do not wrap output in Markdown code blocks, do not append punctuation, and do not include any conversational text.";

            $requestBodyStructure = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $promptInstructions],
                            [
                                "inlineData" => [
                                    "mimeType" => "image/jpeg",
                                    "data" => $cleanBase64Data
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Handshake using Gemini Flash API
            $keys = file_exists(__DIR__ . '/../config/Keys.php') ? include __DIR__ . '/../config/Keys.php' : [];
            $apiKey = $keys['gemini_key_2'] ?? '';
            $endpointUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $ch = curl_init($endpointUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBodyStructure));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $serverRawResponse = curl_exec($ch);
            $responseHttpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($responseHttpStatusCode !== 200) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'API gateway server returned error ' . $responseHttpStatusCode . '. Details: ' . $serverRawResponse
                ]);
                exit();
            }

            $parsedResponseDataArray = json_decode($serverRawResponse, true);
            $identifiedProductKeyword = trim($parsedResponseDataArray['candidates'][0]['content']['parts'][0]['text'] ?? '');
            
            if (empty($identifiedProductKeyword)) {
                echo json_encode(['success' => false, 'error' => 'Image analysis completed but no clear product text could be processed.']);
                exit();
            }

            $cleanedAIKeyword = $this->cleanProductString($identifiedProductKeyword);

            // Isolate brand
            $brands_array = ['garnier', 'skintific', 'cetaphil', 'cosrx', 'medicube', 'glad2glow', 'eucerin', 'aiken'];
            $detected_brand = '';
            foreach ($brands_array as $b) {
                if (strpos(strtolower($identifiedProductKeyword), $b) !== false) {
                    $detected_brand = $b;
                    break;
                }
            }

            // Isolate category/type
            $detected_type = '';
            $types_array = ['micellar', 'cleanser', 'wash', 'toner', 'serum', 'moisturizer', 'moisturiser', 'sunscreen', 'mask'];
            foreach ($types_array as $t) {
                if (strpos(strtolower($identifiedProductKeyword), $t) !== false) {
                    $detected_type = ($t === 'moisturiser') ? 'moisturizer' : $t;
                    break;
                }
            }

            // Query brand matches
            $db_items = Product::getLatestProductsByBrand($detected_brand);

            $best_match_group = [];
            $highest_score = 0;
            $target_match_name = "";

            if (!empty($db_items)) {
                $all_candidates = [];
                foreach ($db_items as $row) {
                    $db_name_lower = strtolower($row['product_name']);
                    $db_cat_lower = strtolower($row['product_category']);
                    
                    if (!empty($detected_type)) {
                        $type_matches = (strpos($db_name_lower, $detected_type) !== false || strpos($db_cat_lower, $detected_type) !== false);
                        if (!$type_matches && $detected_type === 'moisturizer') {
                            $type_matches = (strpos($db_name_lower, 'moisturiser') !== false || strpos($db_name_lower, 'cream') !== false || strpos($db_name_lower, 'gel') !== false);
                        }
                        if (!$type_matches) {
                            continue; 
                        }
                    }

                    $cleanedDBName = $this->cleanProductString($row['product_name']);
                    similar_text($cleanedAIKeyword, $cleanedDBName, $similarity_percent);
                    
                    if ($similarity_percent > $highest_score) {
                        $highest_score = $similarity_percent;
                        $target_match_name = $cleanedDBName; 
                    }
                    
                    $row['similarity_score'] = $similarity_percent;
                    $all_candidates[] = $row;
                }

                if ($highest_score >= 60) { 
                    foreach ($all_candidates as $candidate) {
                        similar_text($target_match_name, $this->cleanProductString($candidate['product_name']), $group_check_score);
                        
                        if ($group_check_score >= 70) {
                            $best_match_group[] = [
                                'id' => $candidate['product_id'],
                                'name' => $candidate['product_name'],
                                'brand' => $candidate['product_brand'],
                                'category' => $candidate['product_category'],
                                'price' => (float)$candidate['product_price'],
                                'store' => $candidate['product_store'],
                                'image' => $candidate['product_image'],
                                'visual_signature' => $candidate['visual_signature']
                            ];
                        }
                    }
                    
                    usort($best_match_group, function($a, $b) {
                        return $a['price'] <=> $b['price'];
                    });
                }
            }

            if (!empty($best_match_group)) {
                echo json_encode([
                    'success' => true,
                    'match_found' => true,
                    'product_keyword' => $identifiedProductKeyword, 
                    'ai_keyword' => $identifiedProductKeyword,      
                    'match_confidence' => round($highest_score, 2) . '%',
                    'products' => $best_match_group
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'match_found' => false,
                    'product_keyword' => $identifiedProductKeyword, 
                    'ai_keyword' => $identifiedProductKeyword,
                    'error_message' => 'Product structurally isolated but variants mismatch active database values.',
                    'products' => []
                ]);
            }

        } catch (Exception $ex) {
            echo json_encode(['success' => false, 'error' => 'System exception caught: ' . $ex->getMessage()]);
        }
        exit();
    }

    /**
     * Executes Vision API call.
     * 
     * @param string $new_image Path or URL to the new image.
     * @param array $candidates Array of candidate products for visual matching.
     * @return string The matching signature or 'NO_MATCH'.
     */
    private function callVisionAPI(string $new_image, array $candidates): string {
        $apiKey = "AIzaSyCYxIUQJuLqBrh8Za1fC_6IBySBCFY14A8"; 
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $prompt = "You are an inventory data auditing system. Compare the physical retail packaging design, text, label layout, and sizing of the target product image against the array of baseline candidate profiles provided below. Identify if the target belongs to an existing signature cluster group.\n\n"
                . "Rules:\n"
                . "1. If the target matches a candidate's product image perfectly, return ONLY the matching 'signature' string value.\n"
                . "2. If there are minor design variations or it is a different product entirely, return 'NO_MATCH'.\n"
                . "3. Do not return any sentences, markdown blocks, formatting wrapper notes, or explanatory text. Just the raw string token.\n\n"
                . "Candidates JSON Metadata Array List:\n" . json_encode($candidates);

        $img_data = @file_get_contents($new_image);
        if (!$img_data) { return 'NO_MATCH'; }

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inlineData" => [
                                "mimeType" => "image/jpeg", 
                                "data" => base64_encode($img_data)
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) { return 'NO_MATCH'; }
        
        $res_data = json_decode($response, true);
        $result_text = trim($res_data['candidates'][0]['content']['parts'][0]['text'] ?? 'NO_MATCH');
        
        return $this->cleanResponseString($result_text);
    }

    /**
     * Cleans the response string from the API by removing markdown elements.
     * 
     * @param string $str Raw response string.
     * @return string Cleaned string.
     */
    private function cleanResponseString(string $str): string {
        $str = str_replace(['`', 'html', 'json', "\n", "\r", " "], '', $str);
        return trim($str);
    }

    /**
     * Cleans a product name string by converting to lowercase and stripping common keywords.
     * 
     * @param string $str Raw product string.
     * @return string Cleaned product string.
     */
    private function cleanProductString(string $str): string {
        $str = strtolower($str);
        $remove_keywords = [
            'moisturiser', 'moisturizer', 'facial', 'face', 'skin', 'oil', 'tea tree',
            'skin naturals', 'all-in-1', 'all in 1', 'even for sensitive skin', 'sensitive'
        ];
        $str = str_replace($remove_keywords, '', $str);
        return trim(preg_replace('/\s+/', ' ', $str));
    }
}
