<?php
// api.php - Backend API untuk data tambahan
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'ticker':
        $symbol = $_GET['symbol'] ?? 'BTCUSDT';
        $data = getBinanceTicker($symbol);
        echo json_encode($data);
        break;
        
    case 'orderbook':
        $symbol = $_GET['symbol'] ?? 'BTCUSDT';
        $data = getBinanceOrderBook($symbol);
        echo json_encode($data);
        break;
        
    case 'trades':
        $symbol = $_GET['symbol'] ?? 'BTCUSDT';
        $data = getBinanceTrades($symbol);
        echo json_encode($data);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getBinanceTicker($symbol) {
    $url = "https://api.binance.com/api/v3/ticker/24hr?symbol=" . $symbol;
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getBinanceOrderBook($symbol) {
    $url = "https://api.binance.com/api/v3/depth?symbol=" . $symbol . "&limit=20";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getBinanceTrades($symbol) {
    $url = "https://api.binance.com/api/v3/trades?symbol=" . $symbol . "&limit=50";
    $response = file_get_contents($url);
    return json_decode($response, true);
}
?>
