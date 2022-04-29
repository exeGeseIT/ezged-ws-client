<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ExeGeseIT\EzGEDWsClient\EzGEDClient;

$config = require __DIR__ . '/config.php';
dump(['config'=>$config]);

$ezWS = (new EzGEDClient( $config['api'] ))
    ->setApiUser($config['user'])
    ->setApiPwd($config['pwd'])
    ->setApiDomain( isset($config['domain']) ? $config['domain'] : null)
    ->setSslVerifyPeer(false)
    ;

/**/
$response = $ezWS->connect(true);
dump([
    'message' => $response->getMessage(),
    'connect' => $response->getHttpHeaders(),
    '$ezWS' => $ezWS,
]);

if ( !empty($config['test-script-file']) ){
    echo '>> Runing: ' . $config['test-script-file'];
    require $config['test-script-file'];
    die();
}
