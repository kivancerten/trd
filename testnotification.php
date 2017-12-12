<?php
namespace TradingBot;


include_once 'secrets.php';
include_once 'PushNotification.php';

$pushNotification = new PushNotification(PUSH_APP_TOKEN, PUSH_USER_KEY);

$pushNotification->send('test', '123');
