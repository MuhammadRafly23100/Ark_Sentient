<?php

require_once 'C:\\xampp\\htdocs\\Mageng\\Ark_Sentient\\vendor\\autoload.php';

// Mengatur header untuk respons JSON dan mengizinkan CORS
// Access-Control-Allow-Origin: Mengizinkan permintaan dari domain frontend Anda.
// $_SERVER['HTTP_ORIGIN'] akan mengambil domain asal permintaan.
// Untuk localhost, ini umumnya aman. Di produksi, pastikan ini adalah domain resmi frontend Anda.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*')); // Aman untuk development, bisa 'http://localhost:8000'
header('Access-Control-Allow-Credentials: true'); // Diperlukan jika Anda mengirim cookies/session
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Metode HTTP yang diizinkan
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); // Header yang diizinkan dari klien

// Tangani preflight OPTIONS request (penting untuk Cross-Origin Resource Sharing - CORS)
// Browser akan mengirim OPTIONS request terlebih dahulu untuk memeriksa izin CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// --- KONFIGURASI AI ---
// !!! PENTING: GANTI 'PASTE_YOUR_GEMINI_API_KEY_HERE' DENGAN API Key Gemini Anda!
// DI PRODUKSI, JANGAN PERNAH MENYIMPAN API KEY LANGSUNG DI KODE. Gunakan environment variables.
$gemini_api_key = 'AIzaSyCDQLa2hNm2v5f6F4uNnJm4QX7Wq11ABck';
$gemini_model = 'gemini-1.5-flash';

// UBAH BARIS INI: Endpoint dengan 'models/' yang eksplisit
$gemini_endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$gemini_model}:generateContent?key={$gemini_api_key}";


// Ambil raw POST data (body JSON) dari permintaan frontend
$input_data = file_get_contents('php://input');
$request_data = json_decode($input_data, true);

// Pastikan data yang diterima adalah JSON valid dan memiliki 'message'
if (json_last_error() !== JSON_ERROR_NONE || !isset($request_data['message'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON input or missing message.']);
    exit();
}

$user_message = $request_data['message'];
$chat_history_from_frontend = $request_data['history'] ?? []; // Ambil riwayat chat dari frontend

$ai_reply = "Maaf, Smart Assistant sedang tidak bisa memproses permintaan Anda saat ini."; // Default reply jika ada error

try {
    $client = new Client();

    // Bangun riwayat chat dalam format yang diharapkan oleh Gemini API
    // Format: [{ "role": "user", "parts": [{ "text": "..." }] }, { "role": "model", "parts": [{ "text": "..." }] }, ...]
    $gemini_contents = [];
    foreach ($chat_history_from_frontend as $chat_entry) {
        $gemini_contents[] = [
            'role' => ($chat_entry['sender'] === 'user') ? 'user' : 'model',
            'parts' => [['text' => $chat_entry['text']]]
        ];
    }
    // Tambahkan pesan pengguna saat ini ke riwayat
    $gemini_contents[] = [
        'role' => 'user',
        'parts' => [['text' => $user_message]]
    ];

    // System instruction: Ini adalah cara untuk "mengajari" AI tentang perannya
    // Berikan persona dan batasan yang Anda inginkan untuk AI Anda
    $system_instruction = [
        'parts' => [
            ['text' => 'Anda adalah Smart Assistant yang ramah dan informatif yang berfokus pada informasi ternak untuk platform ARK Sentient. Tanggapi pertanyaan pengguna dengan bahasa yang mudah dimengerti. Jika pertanyaan tidak terkait ternak atau fitur ARK Sentient (Marketplace Ternak, Pemeriksa Ternak, Sejarah), arahkan pengguna kembali ke topik ternak atau fitur yang relevan, atau jelaskan bahwa Anda tidak dapat membantu dengan topik tersebut.']
        ]
    ];

    // Struktur body permintaan ke Gemini API
    $requestBody = [
        'system_instruction' => $system_instruction, // Mengatur persona AI
        'contents' => $gemini_contents, // Mengirim seluruh riwayat chat (termasuk pesan terbaru)
        'generationConfig' => [
            'maxOutputTokens' => 800, // Batasi panjang respons AI (opsional)
            'temperature' => 0.7, // Tingkat kreativitas AI (0.0=fakta, 1.0=sangat kreatif)
            'topP' => 1,
            'topK' => 1,
        ],
    ];

    $response = $client->post($gemini_endpoint, [
        'json' => $requestBody, // Mengirim data sebagai JSON
        'headers' => [
            'Content-Type' => 'application/json',
            // Tidak perlu 'Authorization' header karena API key ada di URL
        ],
        'verify' => false, // Pertahankan 'true' di produksi untuk keamanan SSL.
                          // Set 'false' HANYA jika Anda punya masalah SSL di lingkungan development lokal.
    ]);

    $statusCode = $response->getStatusCode(); // Dapatkan HTTP status code
    $responseData = json_decode($response->getBody()->getContents(), true); // Dekode respons JSON

    if ($statusCode === 200 && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_reply = $responseData['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Log error yang lebih detail jika respons API tidak sukses atau tidak sesuai format
        error_log("Gemini API Error: Status Code {$statusCode}, Response: " . json_encode($responseData));
        $ai_reply = "Maaf, ada masalah dalam menghubungi Gemini API. Coba lagi nanti. (Kode: {$statusCode})";
        if (isset($responseData['error']['message'])) {
             $ai_reply .= " Detail: " . $responseData['error']['message'];
        }
    }

} catch (RequestException $e) {
    // Tangani error yang terkait dengan request HTTP (misal: masalah koneksi, timeout)
    error_log("Guzzle Request Exception: " . $e->getMessage());
    if ($e->hasResponse()) {
        error_log("Response Body: " . $e->getResponse()->getBody()->getContents());
    }
    $ai_reply = "Maaf, terjadi kesalahan koneksi dengan layanan AI. Pastikan koneksi internet stabil.";
} catch (Exception $e) {
    // Tangani error umum lainnya
    error_log("General Exception: " . $e->getMessage());
    $ai_reply = "Terjadi kesalahan yang tidak terduga di sisi server.";
}

echo json_encode(['reply' => $ai_reply]); // Kirim balasan AI kembali ke frontend
exit();
?>