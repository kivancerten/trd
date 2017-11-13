<?php
namespace TradingBot;

define('BUY_PERCENT_LIMIT', 0.15);
define('SELL_PERCENT_LIMIT', 0.15);
define('DRY_RUN', true);

include_once 'secrets.php';
include_once 'TradingClient.php';
include_once 'TradingBot.php';

$tradingClient = new TradingClient(
    API_KEY,
    SECRET_KEY
);

$tradingBot = new TradingBot($tradingClient, 'USDT_BTC');

$tradingBot->determineLastTrade();
$tradingBot->fetchCurrentPrices();

echo 'USD Balance: ' . $usdBalance = $tradingBot->returnUSDBalance() . PHP_EOL;
echo 'COIN Balance: ' .  $btcBalance = $tradingBot->returnCoinBalance() . PHP_EOL;
echo 'Last trade price: ' . $lastTradePrice = $tradingBot->getLastTradeRate() . PHP_EOL;
echo 'Last trade type: ' . $tradingBot->getLastTradeType() . PHP_EOL;
echo 'Trading fee: ' . $tradingFee = $tradingBot->returnTradingFee() . PHP_EOL;
echo 'Current LowestAsk: ' . $tradingBot->getLowestAsk() . PHP_EOL;
echo 'Current HighestBid: ' . $tradingBot->getHighestBid() . PHP_EOL;

if ($tradingBot->getLastTradeType() == TradingBot::TRADE_TYPE_BUY) {
    //sell
    $sellPrice = $tradingBot->getHighestBid();
    $sellPriceWithFee = $sellPrice - ($sellPrice * $tradingFee);

    echo 'SellPrice: ' . $sellPrice . PHP_EOL;
    echo 'SellPriceWithFee: ' . $sellPriceWithFee . PHP_EOL;

    $profitPercent =  (100 * ($sellPriceWithFee - $lastTradePrice)) / $lastTradePrice;
    echo 'ProfitPercent: ' . $profitPercent . PHP_EOL;

    if ($profitPercent >= SELL_PERCENT_LIMIT) {
        $sellAmount = round($btcBalance / $sellPrice, 6, PHP_ROUND_HALF_DOWN);
        echo  'Sell amount: ' . $sellAmount . PHP_EOL;

        if (! DRY_RUN) {
            $sellResult = $tradingBot->sell($sellPrice, $sellAmount);
            if ($sellResult) {
                echo 'Sell order success orderNumber: ' . $sellResult;
            }
        }

    }

} elseif ($tradingBot->getLastTradeType() == TradingBot::TRADE_TYPE_SELL) {
    //buy
    $buyPrice = $tradingBot->getLowestAsk();
    $buyPriceWithFee = $buyPrice + ($buyPrice * $tradingFee);

    echo 'BuyPrice: ' . $buyPrice . PHP_EOL;
    echo 'BuyPriceWithFee: ' . $buyPriceWithFee . PHP_EOL;

    $profitPercent = (100 * ($lastTradePrice - $buyPriceWithFee)) / $lastTradePrice;
    echo 'ProfitPercent: ' . $profitPercent . PHP_EOL;

    if ($profitPercent >= BUY_PERCENT_LIMIT) {
        $buyAmount = round($usdBalance / $buyPrice, 4, PHP_ROUND_HALF_DOWN);
        echo 'Buy amount: ' . $buyAmount . PHP_EOL;

        if (! DRY_RUN) {
            $buyResult = $tradingBot->buy($buyPrice, $buyAmount);
            if ($buyResult) {
                echo 'Buy order success orderNumber: ' . $buyResult;
            }
        }

    }
}

echo PHP_EOL;
