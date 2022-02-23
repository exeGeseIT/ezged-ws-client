<?php

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseInterface;
use ExeGeseIT\EzGEDWsClient\EzGEDClient;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$httpclient = HttpClient::create([
    'verify_peer' => false,
    'base_uri' => $config['api'] . '/data/',
    'query' => [
        'format' => 'json',
    ],
]);

$ezWS = new EzGEDClient($httpclient, $config['user'], $config['pwd']);

/* */
$reponse = $ezWS->connect(true);
dump([
    'message' => $reponse->getMessage(),
    'connect' => $reponse->getHttpHeaders(),
    '$ezWS' => $ezWS,
]);

/* */
$reponse = $ezWS->getPerimeter();
dump(['getPerimeter' => $reponse]);

/*
*/