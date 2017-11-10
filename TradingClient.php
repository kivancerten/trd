<?php
namespace TradingBot;

class TradingClient
{
    protected $publicUrl = 'https://poloniex.com/public';
    protected $tradingUrl = 'https://poloniex.com/tradingApi';
    protected $apiKey;
    protected $secretKey;

    public function __construct($apiKey, $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * @throws \Exception
     */
    private function query(array $request = array ())
    {

        $microTime = explode(' ', microtime());
        $request['nonce'] = $microTime[1] . substr($microTime[0], 2, 6);

        // generate the POST data string
        $postString = http_build_query($request, '', '&');
        $sign = hash_hmac('sha512', $postString, $this->secretKey);

        // generate the extra headers
        $headers = array (
            'Key: ' . $this->apiKey,
            'Sign: ' . $sign,
        );

        // curl handle (initialize if required)
        static $curlHandler = null;

        if (is_null($curlHandler)) {
            $curlHandler = curl_init();
            curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $curlHandler,
                CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; Poloniex PHP bot; ' . php_uname('a') . '; PHP/' . phpversion() . ')'
            );
        }
        curl_setopt($curlHandler, CURLOPT_URL, $this->tradingUrl);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);

        // run the query
        $result = curl_exec($curlHandler);

        if ($result === false) {
            throw new \Exception('Curl error: ' . curl_error($curlHandler));
        }

        $decodedResponse = json_decode($result, true);
        if (!$decodedResponse) {
            throw new \Exception('Invalid data: '. $result);
        } else {
            return $decodedResponse;
        }
    }

    /**
     * @param string $currencyPair
     * @return \stdClass
     */
    public function returnPublicPrices($currencyPair)
    {
        $prices = file_get_contents($this->publicUrl . '?command=returnTicker');
        $prices = json_decode($prices);
        return $prices->$currencyPair;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function returnBalances()
    {
        return $this->query(['command' => 'returnBalances']);
    }

    /**
     * @param string $currencyPair
     * @return mixed
     * @throws \Exception
     */
    public function returnOpenOrders($currencyPair)
    {
        return $this->query(['command' => 'returnOpenOrders', 'currencyPair' => $currencyPair]);
    }

    /**
     * @return boolean|mixed
     * @throws \Exception
     */
    public function returnFeeInfo()
    {
        return $this->query(['command' => 'returnFeeInfo']);
    }

    /**
     * @param string $currencyPair
     * @param integer $limit
     * @return boolean|mixed
     * @throws \Exception
     */
    public function returnTradeHistory($currencyPair, $limit)
    {
        return $this->query(['command' => 'returnTradeHistory', 'currencyPair' => $currencyPair, 'limit' => $limit]);
    }

    /**
     * @param string $currencyPair
     * @param float $rate
     * @param float $amount
     * @param boolean $fillOrKill
     * @param boolean $immediateOrCancel
     * @return boolean|mixed
     * @throws \Exception
     */
    public function buy($currencyPair, $rate, $amount, $fillOrKill = true, $immediateOrCancel = true)
    {
        return $this->query([
            'command' => 'buy',
            'currencyPair' => $currencyPair,
            'rate' => $rate,
            'amount' => $amount,
            'fillOrKill' => $fillOrKill ? '1' : '0',
            'immediateOrCancel' => $immediateOrCancel ? '1' : '0'
        ]);
    }

    /**
     * @param string $currencyPair
     * @param float $rate
     * @param float $amount
     * @param boolean $fillOrKill
     * @param boolean $immediateOrCancel
     * @return boolean|mixed
     * @throws \Exception
     */
    public function sell($currencyPair, $rate, $amount, $fillOrKill = true, $immediateOrCancel = true)
    {
        return $this->query([
            'command' => 'buy',
            'currencyPair' => $currencyPair,
            'rate' => $rate,
            'amount' => $amount,
            'fillOrKill' => $fillOrKill ? '1' : '0',
            'immediateOrCancel' => $immediateOrCancel ? '1' : '0'
        ]);
    }
}
