<?php
namespace TradingBot;

include_once 'secrets.php';
include_once 'TradingClient.php';
include_once 'TradingBot.php';

$tradingClient = new TradingClient(
    API_KEY,
    SECRET_KEY
);

echo 'Date: ' . date('c') . PHP_EOL;

try {
    $response = $tradingClient->returnBalances();

    foreach ($response as $symbol => $value) {
        printf('%6s: %16s' . PHP_EOL, $symbol, $value);
    }

} catch (\Exception $exception) {
    var_dump($exception);
}
