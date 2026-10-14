<?php
// Server-side WMATA proxy. Keep the subscription key outside the web root.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
function fail($status, $message) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') fail(405, 'Method not allowed');
$key = trim((string)(getenv('WMATA_API_KEY') ?: ''));
if ($key === '') {
    // /home/USER/public_html/bus/api -> /home/USER
    $home = dirname(__DIR__, 3);
    $keyfile = $home . '/.wmata_bus_key';
    if (is_readable($keyfile)) $key = trim((string)file_get_contents($keyfile));
}
if ($key === '') fail(503, 'WMATA API key not configured on server');
if (!function_exists('curl_init')) fail(503, 'PHP cURL extension unavailable');
$url = 'https://api.wmata.com/NextBusService.svc/json/jPredictions?StopID=1001453';
$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => ['api_key: ' . $key, 'Accept: application/json']
]);
$body = curl_exec($curl);
$code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
if ($body === false) fail(502, 'WMATA connection failed');
if ($code !== 200) fail(502, 'WMATA returned HTTP ' . $code);
$data = json_decode($body, true);
if (!isset($data['Predictions']) || !is_array($data['Predictions'])) fail(502, 'Unexpected WMATA response');
$predictions = array_values(array_filter($data['Predictions'], static function ($p) {
    return strtoupper(trim((string)($p['RouteID'] ?? ''))) === 'C91'
        && stripos((string)($p['DirectionText'] ?? ''), 'west') !== false;
}));
echo json_encode(['Predictions' => $predictions], JSON_UNESCAPED_SLASHES);
