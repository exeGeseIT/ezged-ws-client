<?php

use ExeGeseIT\EzGEDWsClient\EzGEDClient;
use ExeGeseIT\EzGEDWsClient\EzGEDHelper;
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

$ezWS = new EzGEDClient($httpclient, $config['user'], $config['pwd'], $config['api']);

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

/**/
$idquery = 3;
$response = $ezWS->search($idquery, null, 5, EzGEDHelper::likeSearchFilter('DOCUMENTRECRUTEMENT_APPLICANT', 'CHA'));
dump(['search' => $response]);

/**/
$idrecord = 249387;
$recordTable = 'DOCUMENTRECRUTEMENT';
$response = $ezWS->getRecordPages($idrecord, $recordTable);
dump(['page' => $response]);

/** /
$fsfileid = 256145;
$fsfileripe = '9258344193e53eb8ef8eb8a5ad25a806492db2b6';
$response = $ezWS->downloadFile($fsfileid, $fsfileripe, './download/');
dump(['download' => $response]);


/*
*/