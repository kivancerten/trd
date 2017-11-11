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
    /** @var string */
    private $currencyPair;

    /**
     * TradingBot constructor.
     * @param TradingClient $tradingClient
     * @param string $currencyPair
     */
    public function __construct(TradingClient $tradingClient, $currencyPair)
    {
        $this->tradingClient = $tradingClient;
        $this->currencyPair = $currencyPair;
    }

    /**
     * @return float
     */
    public function returnTradingFee()
    {
        return (float)$this->tradingClient->returnFeeInfo()['takerFee'];
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
        return (float)$this->balances[explode('_', $this->currencyPair)[0]];
    }

    /**
     * @return float
     */
    public function returnCoinBalance()
    {
        $this->fetchBalances();
        return (float)$this->balances[explode('_', $this->currencyPair)[1]];
    }

    public function determineLastTrade()
    {
        $lastTrade = $this->tradingClient->returnTradeHistory($this->currencyPair, 1)[0];

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
        $priceObject = $this->tradingClient->returnPublicPrices($this->currencyPair);
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
        $result = $this->tradingClient->buy($this->currencyPair, $rate, $amount);

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
        $result = $this->tradingClient->buy($this->currencyPair, $rate, $amount);

        if (isset($result['orderNumber'])) {
            return $result['orderNumber'];
        }

        return false;
    }
}
