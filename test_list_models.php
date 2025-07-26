<?php
// File: test_list_models.php

// Pastikan path ini benar ke folder vendor Anda dari lokasi file ini
require_once 'C:\\xampp\\htdocs\\Mageng\\Ark_Sentient\\vendor\\autoload.php';

header('Content-Type: text/plain'); // Untuk memudahkan melihat output di browser

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$api_key = 'AIzaSyCDQLa2hNm2v5f6F4uNnJm4QX7Wq11ABck';

// Endpoint untuk mendaftar model yang tersedia
$list_models_endpoint = "https://generativelanguage.googleapis.com/v1beta/models?key={$api_key}";

echo "Attempting to list models from: " . htmlspecialchars($list_models_endpoint) . "\n\n";

try {
    $client = new Client();
    // Menggunakan GET request untuk list models
    $response = $client->get($list_models_endpoint, [
        'verify' => false, // Tetap false untuk development lokal, seperti sebelumnya
    ]);

    $statusCode = $response->getStatusCode();
    $responseData = json_decode($response->getBody()->getContents(), true);

    echo "HTTP Code: " . $statusCode . "\n";
    echo "Response:\n";
    print_r($responseData); // Menampilkan seluruh respons API

    if (isset($responseData['models'])) {
        echo "\n\n--- Models yang Mendukung 'generateContent' ---\n";
        foreach ($responseData['models'] as $model) {
            // Periksa jika model mendukung metode 'generateContent'
            if (isset($model['supportedGenerationMethods']) && in_array('generateContent', $model['supportedGenerationMethods'])) {
                echo "Model ID: " . $model['name'] . "\n"; // Ini adalah nama model yang akan digunakan di API
                echo "Display Name: " . $model['displayName'] . "\n"; // Nama yang lebih mudah dibaca
                echo "Version: " . ($model['version'] ?? 'N/A') . "\n";
                echo "Description: " . ($model['description'] ?? 'N/A') . "\n";
                echo "------------------------------------------------\n";
            }
        }
    } else {
        echo "\n\nTidak ada daftar model yang ditemukan atau terjadi error pada respons.\n";
        if (isset($responseData['error']['message'])) {
            echo "Error Detail: " . $responseData['error']['message'] . "\n";
        }
    }

} catch (RequestException $e) {
    echo "Guzzle Request Exception: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response Body: " . $e->getResponse()->getBody()->getContents() . "\n";
    }
} catch (Exception $e) {
    echo "General Exception: " . $e->getMessage() . "\n";
}

echo "\n\nEnd of test.";
?>