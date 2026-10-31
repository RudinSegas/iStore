<?php
$key = getenv('GEMINI_API_KEY');
$model = getenv('GEMINI_MODEL');

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "x-goog-api-key: " . $key,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "contents" => [
            ["parts" => [["text" => "Hello"]]]
        ]
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

$response = curl_exec($ch);
$data = json_decode($response ?: '', true);

echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
echo "API status: " . ($data['error']['status'] ?? 'SUCCESS') . PHP_EOL;
echo "API message: " . ($data['error']['message'] ?? 'Request completed') . PHP_EOL;

if ($response === false) {
    echo "cURL error: " . curl_error($ch) . PHP_EOL;
}

curl_close($ch);
