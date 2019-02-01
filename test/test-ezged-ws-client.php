<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use JcgDev\EzGEDWsClient\Component\Core;
use JcgDev\EzGEDWsClient\EzGEDWsClient;
use Symfony\Component\VarDumper\VarDumper;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

function trace( $reqKey, EzGEDWsClient $ezWS, bool $withRaw = false, $trace = null) {

    echo sprintf("\n ---------- %s ( %s ) ----------", $reqKey, $ezWS->getSessionId());
    if ( null !== $trace ) {
        if ( !is_array($trace) ) {
            $trace = [$trace];
        }
        foreach ($trace as $key => $value) {
            $_val = ( is_null($value) || is_scalar ($value) ) ? $value : json_encode($value);
            echo sprintf("\n %s: %s", $key,$_val);
        }
        echo sprintf("\n ---------- ", $reqKey);
    }
    echo sprintf("\n   STATUS >> [ %s ] - %s", $ezWS->getRequestStatusCode(),$ezWS->getRequestStatusMessage());
    echo sprintf("\n    ERROR >> [ %s ] - %s", $ezWS->getErrorCode(),$ezWS->getErrorMessage());
    if ( $withRaw ) {
        echo sprintf("\n  RAW >> %s", json_encode($ezWS->getRawJsonResponse(),JSON_PRETTY_PRINT) );
    }
    echo sprintf("\n RESPONSE >> ", $reqKey);
    echo sprintf("\n %s ", json_encode($ezWS->getResponse(),JSON_PRETTY_PRINT));
    echo sprintf("\n -------------------- ^ --------------------\n", $reqKey);
    
}

try {

    $httpRequestTraceHandler = fopen( __DIR__ . '/httprequest.log','a');

    $ezWS = new EzGEDWsClient($config->api,$config->user,$config->pwd, $httpRequestTraceHandler);

    $ezWS->connect();
    trace('connect: '.Core::REQ_AUTH, $ezWS);

    $ezWS->getPerimeter();
    trace('getPerimeter: '.Core::REQ_GET_PERIMETER, $ezWS);
    
    $ezWS->logout();
    trace('logout: '.Core::REQ_LOGOUT, $ezWS);
    $ezWS->requestView(36,0,20);
    trace('requestView: '.Core::REQ_REQUEST_VIEW, $ezWS, false, ['qyrid'=>36, 'offset'=>0, 'limit'=>20]);

    /*
    $ezWS->requestView(36,2,2);
    trace('requestView: '.Core::REQ_REQUEST_VIEW, $ezWS, false, ['qyrid'=>36, 'offset'=>1, 'limit'=>2]);
    */

} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
} catch (Exception $ex) {
     dump( $ex->getMessage() );
}

