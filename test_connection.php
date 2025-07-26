<?php
// File: C:\xampp\htdocs\Mageng\Ark_Sentient\test_connection.php

// Pastikan path ini benar ke folder vendor Anda dari lokasi file ini
require_once 'C:\\xampp\\htdocs\\Mageng\\Ark_Sentient\\vendor\\autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// --- KONFIGURASI AI ---
// !!! PENTING: GANTI 'PASTE_YOUR_GEMINI_API_KEY_HERE' DENGAN API Key Gemini Anda yang VALID!
// Jangan biarkan kosong atau placeholder.
$gemini_api_key = 'AIzaSyCDQLa2hNm2v5f6F4uNnJm4QX7Wq11ABck';

// Model ID yang benar (sesuai hasil test_list_models.php)
$gemini_model = 'gemini-1.5-flash';

// Endpoint yang benar (tanpa '/models/' di $gemini_model)
$gemini_endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$gemini_model}:generateContent?key={$gemini_api_key}";

echo "Attempting to connect to: " . htmlspecialchars($gemini_endpoint) . "<br>";
echo "Sending payload: {\"contents\":[{\"role\":\"user\",\"parts\":[{\"text\":\"Hello, world!\"}]}]}<br><br>";

$payload = json_encode([
    'contents' => [
        [
            'role' => 'user',
            'parts' => [['text' => 'Hello, world!']]
        ]
    ]
]);

$ch = curl_init($gemini_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Matikan verifikasi SSL untuk tes ini

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<h2>cURL Test Result:</h2>";
echo "<p>HTTP Code: " . htmlspecialchars($info['http_code']) . "</p>";
echo "<h3>Response (Raw):</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "<h3>cURL Error:</h3>";
echo "<pre>" . htmlspecialchars($error) . "</pre>";
echo "<h3>cURL Info:</h3>";
echo "<pre>" . print_r($info, true) . "</pre>";
?>