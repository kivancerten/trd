<?php
namespace TradingBot;

define('BUY_PERCENT_LIMIT', 1.25);
define('SELL_PERCENT_LIMIT', 1.25);
define('DRY_RUN', false);

include_once 'secrets.php';
include_once 'TradingClient.php';
include_once 'TradingBot.php';
include_once 'PushNotification.php';


/**
 * @param float $float
 * @return string
 */
function floatAsString($float)
{
    return number_format($float, 8, '.', '');
}

/**
 * @param string $key
 * @param mixed $value
 */
function printLine($key, $value)
{
    echo sprintf('%20s: %20s', $key, $value) . PHP_EOL;
}

/**
 * @param TradingBot $tradingBot
 * @param PushNotification $pushNotification
 * @param string $priceAsString
 * @param string $amount
 * @param string $title
 */
function sendPush(TradingBot $tradingBot, PushNotification $pushNotification, $priceAsString, $amount, $title)
{
    $tradingBot->clearBalances();
    $pushNotification->send(
        $title,
        "Price: $priceAsString\nAmount: $amount\n" .
        $tradingBot->getBaseCurrency() . ': ' . $tradingBot->returnBaseCurrencyBalance() . "\n" .
        $tradingBot->getCounterCurrency() . ': ' . $tradingBot->returnCounterCurrencyBalance()
    );
}


$pushNotification = new PushNotification(PUSH_APP_TOKEN, PUSH_USER_KEY);

$tradingClient = new TradingClient(
    API_KEY,
    SECRET_KEY
);


/*
$a = $tradingClient->returnTradeHistory('USDT_BTC', 10);
var_dump($a);
exit;
*/


$tradingBot = new TradingBot($tradingClient, 'USDT_BTC');

echo 'Date: ' . date('c') . PHP_EOL;

$tradingBot->determineLastTrade();
$tradingBot->fetchCurrentPrices();

$lastTradePrice = $tradingBot->getLastTradeRate();
$baseCurrencyBalance = floatAsString($tradingBot->returnBaseCurrencyBalance());
$counterCurrencyBalance = floatAsString($tradingBot->returnCounterCurrencyBalance());

printLine('C1Balance', $baseCurrencyBalance . ' ' . $tradingBot->getBaseCurrency());
printLine('C1Balance', $counterCurrencyBalance . ' ' . $tradingBot->getCounterCurrency());
printLine('LstTrdPrc', floatAsString($lastTradePrice));
printLine('LstTrdTyp', $tradingBot->getLastTradeType());
printLine('TrdFee', $tradingFee = $tradingBot->returnTradingFee());
printLine('LowestAsk', floatAsString($tradingBot->getLowestAsk()));
printLine('HighestBid', floatAsString($tradingBot->getHighestBid()));


if ($tradingBot->getLastTradeType() == TradingBot::TRADE_TYPE_BUY) {
    //sell
    $sellPrice = $tradingBot->getHighestBid();
    $sellPriceWithFee = $sellPrice - ($sellPrice * $tradingFee);
    $profitPercent =  (100 * ($sellPriceWithFee - $lastTradePrice)) / $lastTradePrice;

    $sellPriceAsString = floatAsString($sellPrice);
    $sellPriceWithFeeAsString = floatAsString($sellPriceWithFee);

    printLine('SellPrice', $sellPriceAsString);
    printLine('SellPriceWithFee', $sellPriceWithFeeAsString);
    printLine('TargetProfit', SELL_PERCENT_LIMIT);
    printLine('ProfitPercent', $profitPercent);

    $sellAmount = round($counterCurrencyBalance, 6, PHP_ROUND_HALF_DOWN);
    $sellAmount *= 0.999;

    printLine('Sell amount', $sellAmount);

    if ($profitPercent >= SELL_PERCENT_LIMIT) {
        if (! DRY_RUN) {
            $sellResult = $tradingBot->sell($sellPriceAsString, $sellAmount);
            if ($sellResult) {
                printLine('Status', 'SellOrderSuccess ' . $sellResult);
                sendPush($tradingBot, $pushNotification, $sellPriceAsString, $sellAmount, 'SellOrderSuccess');

            } else {
                var_dump($sellResult);
            }
        }
    } else {
        printLine('Status', 'Profit NOT enough');
    }

} elseif ($tradingBot->getLastTradeType() == TradingBot::TRADE_TYPE_SELL) {
    //buy
    $buyPrice = $tradingBot->getLowestAsk();
    $buyPriceWithFee = $buyPrice + ($buyPrice * $tradingFee);
    $profitPercent = (100 * ($lastTradePrice - $buyPriceWithFee)) / $lastTradePrice;

    $buyPriceAsString = floatAsString($buyPrice);
    $buyPriceWithFeeAsString = floatAsString($buyPriceWithFee);

    printLine('BuyPrice', $buyPriceAsString);
    printLine('BuyPriceWithFee', $buyPriceWithFeeAsString);
    printLine('TargetProfit', BUY_PERCENT_LIMIT);
    printLine('ProfitPercent', $profitPercent);

    $buyAmount = round($baseCurrencyBalance / $buyPrice, 4, PHP_ROUND_HALF_DOWN);
    $buyAmount *= 0.999;

    printLine('Buy amount', $buyAmount);

    if ($profitPercent >= BUY_PERCENT_LIMIT) {
        if (! DRY_RUN) {
            $buyResult = $tradingBot->buy($buyPriceAsString, $buyAmount);
            if ($buyResult) {
                printLine('Status', 'BuyOrderSuccess ' . $buyResult);
                sendPush($tradingBot, $pushNotification, $buyPriceAsString, $buyAmount, 'BuyOrderSuccess');
            } else {
                var_dump($buyResult);
            }
        }
    } else {
        printLine('Status', 'Profit NOT enough');
    }
}

echo PHP_EOL;
