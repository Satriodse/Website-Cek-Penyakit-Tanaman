<?php
header('Content-Type: application/json');

$url = "https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/2506/th/126/key/d034dfbaf31081d490ec63859f2daa49";

$context = stream_context_create([
    'http' => [
        'method'        => 'GET',
        'timeout'       => 15,
        'ignore_errors' => true,
        'header'        => "User-Agent: Mozilla/5.0\r\n"
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(["error" => "Gagal mengambil data dari BPS. Pastikan server bisa akses internet."]);
    exit;
}

// Kirim response asli ke frontend (apa adanya dari BPS)
echo $response;
?>