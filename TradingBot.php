<?php
namespace TradingBot;

class TradingBot
{
    const TRADE_TYPE_SELL = 'sell';
    const TRADE_TYPE_BUY = 'buy';

    /** @var TradingClient */
    private $tradingClient;

    /** @var array */
    private $balances = [];

    /** @var string */
    private $lastTradeType;
    /** @var float */
    private $lastTradeRate;
    /** @var float */
    private $lowestAsk;
    /** @var float */
    private $highestBid;

    /**
     * TradingBot constructor.
     * @param TradingClient $tradingClient
     */
    public function __construct(TradingClient $tradingClient)
    {
        $this->tradingClient = $tradingClient;
    }

    /**
     * @return float
     */
    public function returnTradingFee()
    {
        return (float)$this->tradingClient->returnFeeInfo()['takerFee'];
    }

    public function returnOpenUSDOrders()
    {
        return $this->tradingClient->returnOpenOrders('USDT_BTC');
    }

    public function returnOpenBTCOrders()
    {
        return $this->tradingClient->returnOpenOrders('BTC_USDT');
    }

    private function fetchBalances()
    {
        if (empty($this->balances)) {
            $this->balances = $this->tradingClient->returnBalances();
        }
    }

    /**
     * @return float
     */
    public function returnUSDBalance()
    {
        $this->fetchBalances();
        return (float)$this->balances['USDT'];
    }

    /**
     * @return float
     */
    public function returnBTCBalance()
    {
        $this->fetchBalances();
        return (float)$this->balances['BTC'];
    }

    public function determineLastTrade()
    {
        $lastTrade = $this->tradingClient->returnTradeHistory('USDT_BTC', 1)[0];

        $this->lastTradeRate = (float)$lastTrade['rate'];
        $this->lastTradeType = $lastTrade['type'] == static::TRADE_TYPE_BUY ? static::TRADE_TYPE_BUY : static::TRADE_TYPE_SELL;
    }

    /**
     * @return string
     */
    public function getLastTradeType()
    {
        return $this->lastTradeType;
    }

    /**
     * @return float
     */
    public function getLastTradeRate()
    {
        return $this->lastTradeRate;
    }

    public function fetchCurrentPrices()
    {
        $priceObject = $this->tradingClient->returnPublicPrices('USDT_BTC');
        $this->lowestAsk = (float)$priceObject->lowestAsk;
        $this->highestBid = (float)$priceObject->highestBid;
    }

    /**
     * @return float
     */
    public function getLowestAsk()
    {
        return $this->lowestAsk;
    }

    /**
     * @return float
     */
    public function getHighestBid()
    {
        return $this->highestBid;
    }

    /**
     * @param float $rate
     * @param float $amount
     * @return boolean|float
     */
    public function buy($rate, $amount)
    {
        $result = $this->tradingClient->buy('USDT_BTC', $rate, $amount);

        if (isset($result['orderNumber'])) {
            return $result['orderNumber'];
        }

        return false;
    }

    /**
     * @param float $rate
     * @param float $amount
     * @return boolean|float
     */
    public function sell($rate, $amount)
    {
        $result = $this->tradingClient->buy('USDT_BTC', $rate, $amount);

        if (isset($result['orderNumber'])) {
            return $result['orderNumber'];
        }

        return false;
    }
}
