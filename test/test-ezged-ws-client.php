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


    //$ezWS->connect()->trace();
    //$ezWS->logout()->trace();


    $ezWS->getPerimeter()->trace();
    

    $idquery = 78;
    
    $ezWS->requestView($idquery,0,20)->trace();

    /*
    $filter = ['field' => 'FACTURE_SCOPE_LBL', 'operator' => 'like', 'value' => 'inf'];
    $ezWS->requestView($idquery,0,5,$filter)->trace();
     */

    $ezWS->getRecordPages(1,'NOTEDEFRAIS')->trace();

    /**
     * Création
     */
    $idrow = null;
    $testFile = __DIR__ . '/../documentation/ezGED-api-webservices-json.pdf';

    $uploadParams = [
        'waitdir'=>'ws-test',
        'name'=> sprintf('ws-upload_%s.pdf',$ezWS->getSessionId()),
    ];

    if ( $ezWS->upload($testFile,$uploadParams)->trace(true)->isSucceed() ) {
        
        $serverFilePath = ($ezWS->getResponse())[0]->filePath;
        dump( sprintf('serverFilePath: %s',$serverFilePath));


        $fields = [
            'NOTEDEFRAIS_SALARIE' => 'JCG',
            'NOTEDEFRAIS_DATE' => date('Y-m-d H:i:s'),
            'NOTEDEFRAIS_NOM_FOU' => 'EzGEDWsClient',
            'NOTEDEFRAIS_MONTANT_TTC' => 130,
            'NOTEDEFRAIS_SOURCE' => 1,
        ];

        if ( $ezWS->createRecord('NOTEDEFRAIS',$fields,$idquery)->trace()->isSucceed() ) {
            $idrow = ($ezWS->getResponse())[0]->RETID;
            dump( sprintf('idrow: %d',$idrow));

            if ( $ezWS->addRecordPage($idrow,'NOTEDEFRAIS', $serverFilePath)->trace()->isSucceed() ) {
                $idjob = ($ezWS->getResponse())[0]->JOBQUEUE_ID;
                dump( sprintf('idjob: %d',$idjob));

                $ezWS->getJobStatus($idjob)->trace();
            }
        }
    }

    if ( null !== $idrow ) {
        $ezWS->getRecordPages($idrow,'NOTEDEFRAIS')->trace();

        $ezWS->updateRecord($idrow,'NOTEDEFRAIS','NOTEDEFRAIS_ID',['NOTEDEFRAIS_NOM_FOU'=>'EzGEDWsClient MAJ'])->trace();
    }


} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
} catch (Exception $ex) {
     dump( [get_class($ex) => $ex->getMessage()], $ex );
}

