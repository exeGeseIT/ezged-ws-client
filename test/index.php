<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ExeGeseIT\EzGEDWsClient\EzGEDClient;
use ExeGeseIT\EzGEDWsClient\EzGEDHelper;

$config = require __DIR__ . '/config.php';
dump(['config'=>$config]);

//$ezWS = new EzGEDClient( $config['api'], $httpclient=null, $config['user'], $config['pwd'], $sslVerifyPeer=false);
$ezWS = (new EzGEDClient( $config['api'] ))
    ->setApiUser($config['user'])
    ->setApiPwd($config['pwd'])
    ->setSslVerifyPeer(false)
    ;

/** /
$response = $ezWS->connect(true);
dump([
    'message' => $response->getMessage(),
    'connect' => $response->getHttpHeaders(),
    '$ezWS' => $ezWS,
]);

/** /
$response = $ezWS->logout();
dump([
    '$ezWS' => $ezWS,
]);

/** /
$response = $ezWS->getPerimeter();
dump(['getPerimeter' => $response]);

/** /
$idquery = 3;
$response = $ezWS->search($idquery, null, 5, EzGEDHelper::likeSearchFilter('DOCUMENTRECRUTEMENT_APPLICANT', 'CHA'));
dump(['search' => $response]);

/** /
$idrecord = 249387;
$recordTable = 'DOCUMENTRECRUTEMENT';
$response = $ezWS->getRecordPages($idrecord, $recordTable);
dump(['page' => $response]);

/** /
$fsfileid = 256145;
$fsfileripe = '9258344193e53eb8ef8eb8a5ad25a806492db2b6';
$response = $ezWS->downloadFile($fsfileid, $fsfileripe, './download/');
dump(['download' => $response]);

 /**/
$fullFilename = __DIR__ . '/../documentation/ezGED-api-webservices-json.pdf';
$waitdir = 'ezged-ws-client-test';
$uploadedname = 'test-upload-v2.x.pdf';
$response = $ezWS->upload($fullFilename, $uploadedname, $waitdir);
dump(['upload' => $response]);


/*
*/