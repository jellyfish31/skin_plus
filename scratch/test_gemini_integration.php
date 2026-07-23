<?php
// scratch/test_gemini_integration.php

header('Content-Type: text/plain');

echo "=== GOOGLE GEMINI API INTEGRATION TEST ===\n\n";

// 1. Load active API keys
$keys_path = __DIR__ . '/../config/Keys.php';
if (!file_exists($keys_path)) {
    echo "❌ Error: Cannot find config/Keys.php at $keys_path\n";
    exit();
}

$keys = require $keys_path;
$api_key = $keys['gemini_key_1'] ?? '';

if (empty($api_key)) {
    echo "❌ Error: gemini_key_1 is empty in config/Keys.php\n";
    exit();
}

echo "🔑 API Key loaded successfully (starts with: " . substr($api_key, 0, 8) . "...)\n";

// 2. Setup endpoint URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;
echo "🌐 API Endpoint: https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash\n\n";

// 3. Define a small base64 pixel image to simulate camera uploads (1x1 transparent pixel)
$dummy_base64_image = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==";

// 4. Construct JSON Payload
$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Identify if this is a valid image transmission. Respond with exactly 'SUCCESS: IMAGE_RECEIVED' if you can read this message, do not include any other text."
                ],
                [
                    "inlineData" => [
                        "mimeType" => "image/png",
                        "data" => $dummy_base64_image
                    ]
                ]
            ]
        ]
    ]
];

$json_payload = json_encode($payload);
echo "📤 Outgoing JSON payload length: " . strlen($json_payload) . " bytes\n";
echo "🔄 Initializing PHP cURL request...\n";

// 5. Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass local SSL issues if any

// 6. Execute request
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 7. Output Results
echo "\n=== INTEGRATION TEST RESULTS ===\n";
if (!empty($curl_error)) {
    echo "❌ cURL Connection Error: " . $curl_error . "\n";
} else {
    echo "📮 HTTP Status Code: " . $http_status . "\n";
    if ($http_status === 200) {
        echo "✅ Integration successful! Gemini API connected.\n\n";
        
        $decoded = json_decode($response, true);
        $ai_text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        echo "🤖 Gemini AI Response: " . trim($ai_text) . "\n\n";
        echo "📄 Full JSON Response from Google:\n" . $response . "\n";
    } else {
        echo "❌ Integration failed. Google returned an error status.\n";
        echo "📄 Response:\n" . $response . "\n";
    }
}
