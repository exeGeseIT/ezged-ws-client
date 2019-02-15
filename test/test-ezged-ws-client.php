<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use GuzzleHttp\Psr7;
use JcgDev\EzGEDWsClient\Component\Helper\EzJobstatus;
use JcgDev\EzGEDWsClient\Exception\RequestException;
use JcgDev\EzGEDWsClient\EzGEDWsClient;
use Symfony\Component\VarDumper\VarDumper;


try {

    $traceLog = __DIR__ . '/tracelog.log';
    $httpRequestTraceHandler = fopen( __DIR__ . '/httprequest.log','a');

    $ezWS = new EzGEDWsClient($config->api,$config->user,$config->pwd, $httpRequestTraceHandler);
    $ezWS->setTraceLogHandler($traceLog);

    // data/showdocs.php?fsfileid=2224&fsfileripe=345e04d4df8114924264fab40b21a6686e5e1850&mobile=1
    //$ezWS->showFile(2224,'345e04d4df8114924264fab40b21a6686e5e1850',__DIR__.'/download.jpg')->trace();
    //dd($ezWS->getErrorCode());
    /*
    $ezWS->connect()->trace();
    dump( sprintf('sessionid: %s',$ezWS->getSessionId()));
    $ezWS->logout()->trace();
    $ezWS->connect(true)->trace(true);
    dump( json_encode($ezWS->getResponse()));
     */


    $ezWS->getPerimeter()->trace();
    //dump( $ezWS->getEzResponse() );
    //$ezFamily = (new EzFamily())->init( $ezWS->getResponse()[0]->rows[0] );
    //dump($ezFamily->getElements()[0]);
    

    $idquery = 78;
    
    /*
    $ezWS->requestView($idquery,0,20)->trace();
     */
    $filter = ['field' => 'NOTEDEFRAIS_MONTANT_TTC', 'operator' => '>=', 'value' => '130'];
    $ezWS->requestView($idquery,0,5,$filter)->trace();

    foreach ( $ezWS->getEzResponse() as $ezRow) {
        dump(sprintf('%d: %s >> %d€ le %s',$ezRow->getId(),$ezRow->NOTEDEFRAIS_SALARIE,$ezRow->NOTEDEFRAIS_MONTANT_TTC,$ezRow->NOTEDEFRAIS_DATE));
    }

    $ezWS->getRecordPages(1,'NOTEDEFRAIS')->trace(true);

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
        
        //$serverFilePath = $ezWS->getResponse()[0]->filePath;
        $serverFilePath = $ezWS->getEzResponse()->getFilePath();
        dump( sprintf('serverFilePath: %s',$serverFilePath));

        $fields = [
            'NOTEDEFRAIS_SALARIE' => sprintf('WS-%s',date('Ymd')),
            'NOTEDEFRAIS_DATE' => date('Y-m-d H:i:s'),
            'NOTEDEFRAIS_NOM_FOU' => 'EzGEDWsClient',
            'NOTEDEFRAIS_MONTANT_TTC' => 10,
            'NOTEDEFRAIS_SOURCE' => 1,
        ];

        if ( $ezWS->createRecord('NOTEDEFRAIS',$fields,$idquery)->trace()->isSucceed() ) {
            $idrow = $ezWS->getEzResponse()->getId();
            dump( sprintf('idrow: %d',$idrow));

            if ( $ezWS->addRecordPage($idrow,'NOTEDEFRAIS', $serverFilePath)->trace()->isSucceed() ) {
                //$idjob = $ezWS->getResponse()[0]->JOBQUEUE_ID;
                $idjob = $ezWS->getEzResponse()->getId();
                dump( sprintf('idjob: %d',$idjob));

                $ezWS->getJobStatus($idjob)->trace();
                //$ewJobstatus = (new EzJobstatus())->init( $ezWS->getResponse()[0] );
                /**
                 * @var EzJobstatus $ewJobstatus
                 */
                $ewJobstatus = $ezWS->getEzResponse();
                dump( sprintf('job status: %s',$ewJobstatus->getStatus()) );
                //dump($ewJobstatus->getData());
            }
        }
    }

    if ( null !== $idrow ) {

        $majFields = [
            'NOTEDEFRAIS_NOM_FOU' => 'EzGEDWsClient MAJ',
            'NOTEDEFRAIS_MONTANT_TTC' => rand(5,200),
            'NOTEDEFRAIS_MOTIF_ID' => 6,
        ];
        $ezWS->updateRecord($idrow,'NOTEDEFRAIS','NOTEDEFRAIS_ID',$majFields)->trace();

        $ezWS->getRecordPages($idrow,'NOTEDEFRAIS')->trace();
        foreach ( $ezWS->getEzResponse() as $ezBag ) {
            dump( $ezBag->getData() );
        }
    }


} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
} catch (Exception $ex) {
     dump( [get_class($ex) => $ex->getMessage()], $ex );
}

