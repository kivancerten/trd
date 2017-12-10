<?php
namespace TradingBot;

class PushNotification
{
    private $appToken;
    private $userKey;

    /**
     * PushNotification constructor.
     * @param $appToken
     * @param $userKey
     */
    public function __construct($appToken, $userKey)
    {
        $this->appToken = $appToken;
        $this->userKey = $userKey;
    }

    public function send($title, $message)
    {
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                "token" => $this->appToken,
                "user" => $this->userKey,
                "message" => $message,
                "title" => $title,
            ),
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        curl_exec($ch);
        curl_close($ch);
    }

}
