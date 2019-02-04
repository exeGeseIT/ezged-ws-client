<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use JcgDev\EzGEDWsClient\EzGEDWsClient;
use Symfony\Component\VarDumper\VarDumper;


try {

    $traceLog = __DIR__ . '/tracelog.log';
    $httpRequestTraceHandler = fopen( __DIR__ . '/httprequest.log','a');

    $ezWS = new EzGEDWsClient($config->api,$config->user,$config->pwd, $httpRequestTraceHandler);

    $ezWS->setTraceLogHandler($traceLog);

    $ezWS->connect()->trace();

    $ezWS->getPerimeter()->trace();
    
    $ezWS->logout()->trace();
    
    $ezWS->requestView(78,0,20)->trace();

    $filter = ['field' => 'FACTURE_SCOPE_LBL', 'operator' => 'like', 'value' => 'inf'];
    $ezWS->requestView(78,0,5,$filter)->trace();


} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
} catch (Exception $ex) {
     dump( $ex->getMessage() );
}

