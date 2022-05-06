<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ExeGeseIT\EzGEDWsClient\EzGEDClient;

try {
    $config = require __DIR__ . '/config.php';
    dump(['config'=>$config]);

    $ezWS = (new EzGEDClient( $config['api'] ))
        ->setApiUser($config['user'])
        ->setApiPwd($config['pwd'])
        ->setApiDomain( isset($config['domain']) ? $config['domain'] : null)
        ->setSslVerifyPeer(false)
        ;

    /**/
    $response = $ezWS->connect();
    dump([
        'message' => $response->getMessage(),
        'connect' => $response->getHttpHeaders(),
        '$ezWS' => $ezWS,
    ]);

    if ( !empty($config['test-script-file']) ){
        echo '>> Runing: ' . $config['test-script-file'];
        require $config['test-script-file'];
    }

    /**/
    dump(['logout' => $ezWS->logout()]);
    
} catch (\Exception $e) {
    dump([
        'Exception throwed' => get_class($e),
        'message' => $e->getMessage(),
        '$exception' => $e,
    ]);
}
