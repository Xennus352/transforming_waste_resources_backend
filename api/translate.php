<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $text = $data['text'];

    // Call the translation API
    $translatedText = translateText($text);
    
    echo json_encode(["translatedText" => $translatedText]);
}

function translateText($text) {
    $apiKey = 'sk-1fbc949953e94b16bff5acf94a6550c5'; // Replace with your DeepL API key
    $url = 'https://api-free.deepl.com/v2/translate';

    $data = [
        'auth_key' => $apiKey,
        'text' => $text,
        'target_lang' => strtoupper('my') // DeepL expects language codes in uppercase
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    
    // Check for errors
    if (isset($responseData['translations'][0]['text'])) {
        return $responseData['translations'][0]['text'];
    } else {
        return "Error: Unable to translate text.";
    }
}
?>