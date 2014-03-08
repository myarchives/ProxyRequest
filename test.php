<?php
require_once('ProxyRequest.php');

$url = 'http://www.example.com/';
$proxy_ip = '192.0.2.0';
$port = '8123';

$proxy = new ProxyRequest();
$proxy->set_request_info($url, $proxy_ip, $port);
$result = $proxy->send_request();
var_dump($result);
