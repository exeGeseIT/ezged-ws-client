<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = (object) [
  'api' => 'https://docenligne.fr/ezged3',
  'user' => 'wsuser',
  'pwd' => 'C0mmuniquerDoc4GED',
];

use JcgDev\EzGEDWsClient\Component\Core;
use JcgDev\EzGEDWsClient\EzGEDWsClient;
use Symfony\Component\VarDumper\VarDumper;

function trace( $reqKey, EzGEDWsClient $ezWS, bool $withRaw = false, $trace = null) {

    echo sprintf("\n ---------- %s ( %s ) ----------", $reqKey, $ezWS->getSessionId());
    echo sprintf("\n STATUS >> [ %s ] - %s", $ezWS->getRequestStatusCode(),$ezWS->getRequestStatusMessage());
    echo sprintf("\n  ERROR >> [ %s ] - %s", $ezWS->getErrorCode(),$ezWS->getErrorMessage());
    if ( $withRaw ) {
        echo sprintf("\n RAW >> %s", var_export($ezWS->getRawJsonResponse(),true) );
    }

    if ( null !== $trace ) {
        if ( !is_array($trace) ) {
            $trace = [$trace];
        }
        echo sprintf("\n ---------- ", $reqKey);
        foreach ($trace as $key => $value) {
            $_val = ( is_null($value) || is_scalar ($value) ) ? $value : var_export($value,true);
            echo sprintf("\n %s: %s", $key,$_val);
        }
    }



    echo sprintf("\n -------------------- ^ --------------------\n", $reqKey);
    
}

try {

    $ezWS = new EzGEDWsClient($config->api,$config->user,$config->pwd);

    $ezWS->connect();
    trace(Core::REQ_AUTH, $ezWS);

    $perimeter = $ezWS->getPerimeter();
    trace(Core::REQ_GET_PERIMETER, $ezWS, false, $perimeter);
    
    $ezWS->logout();
    trace(Core::REQ_LOGOUT, $ezWS);

    $result = $ezWS->requestView(36);
    trace(Core::REQ_REQUEST_VIEW, $ezWS, false, $result);

} catch (Exception $ex) {
     dump( $ex->getMessage() );
}

