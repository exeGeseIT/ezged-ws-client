<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzFamily;
use ExeGeseIT\EzGEDWsClient\Core\Dto\EzGenericBag;
use ExeGeseIT\EzGEDWsClient\Core\Dto\EzJob;
use ExeGeseIT\EzGEDWsClient\Core\Dto\EzJobstatus;
use ExeGeseIT\EzGEDWsClient\Core\Dto\EzRow;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDServiceConfigurator;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDServicesInterface;

/**
 * Description of Services
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzGEDAbstract implements EzGEDServicesInterface
{

    const ERRORCODE_OK = 0;
    
    private static array $services;

    /**
     *
     * @param string $serviceKey
     * @return EzGEDServiceConfigurator
     */
    protected function getServiceConfig(string $serviceKey): ?EzGEDServiceConfigurator
    {
        return array_key_exists($serviceKey, self::$services) ? self::$services[$serviceKey] : null;
    }
    
    
    public function __construct()
    {
        self::$services = $this->initServices();
    }


    /**
     *
     * @return array
     */
    private function initServices(): array
    {
        $fns = [

            self::REQ_UPLOAD => function(array $reponse){
                $ezBag = (new EzGenericBag())->init( $reponse[0] );
                return $ezBag;
            },

            self::REQ_GET_PERIMETER => function(array $reponse){
                $r = $reponse[0]->rows;
                $out = [];
                foreach ($r as $stdClass) {
                    $ezFamily = (new EzFamily())->init( $stdClass );
                    $out[ $ezFamily->getId() ] = $ezFamily;
                }
                return $out;
            },

            self::REQ_EXEC_REQUEST => function(array $reponse){
                $out = [];
                foreach ($reponse as $stdClass) {
                    $ezQuery = (new EzRow())->init( $stdClass );
                    $out[ $ezQuery->getId() ] = $ezQuery;
                }
                return $out;
            },

            self::REQ_GET_RECORD_FILES => function(array $reponse){
                $out = [];
                foreach ($reponse as $stdClass) {
                    $ezBag = (new EzGenericBag())->init( $stdClass );
                    $out[ $ezBag->getRank() ] = $ezBag;
                }
                return $out;
            },

            self::REQ_CREATE_RECORD => function(array $reponse){
                $ezBag = (new EzGenericBag('RETID'))->init( $reponse[0] );
                return $ezBag;
            },

            self::REQ_ADD_RECORD_FILE => function(array $reponse){
                $ezJob = (new EzJob())->init( $reponse[0] );
                return $ezJob;
            },

            self::REQ_GET_JOB_STATUS => function(array $reponse){
                $ezJobstatus = (new EzJobstatus())->init( $reponse[0] );
                return $ezJobstatus;
            },
            
        ];



        $services = [];

        // Authent: sec/authenticate
        $services[ self::REQ_AUTH ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('sec/authenticate')
            ->setMethod('GET')
            ->setQuery([
                'login' => '',
                'pwd' => '',
            ])
            ->setResponseFilter([
                'sessionid',
            ]);

        // KeepAlive: secses/keepalive
        $services[ self::REQ_AUTH_KEEPALIVE ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('secses/keepalive')
            ->setMethod('POST')
            ->setResponseFilter([
                'countsignbook',
                'countcorrection',
                'counttrash',
                'countmessage',
                'countworkflow',
            ]);

        // Logout: secses/delete
        $services[ self::REQ_LOGOUT ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('secses/delete')
            ->setMethod('GET')
            ->setQuery([
                'sessionid' => '',
                'secsesid' => '',
            ])
            ->setResponseFilter([]);

        // Récupérer un fichier
        $services[ self::REQ_DOWNLOAD_FILE ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('showdocs.php')
            ->setMethod('GET')
            ->setQuery([
                'fsfileid' => 0,
                'fsfileripe' => 'n0p',

                'mode' => null,
                'download' => null,
                'mobile' => null, //mobile =1 force 'download'
            ]);

        // Upload d'un Fichier
        $services[ self::REQ_UPLOAD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('pupload.php')
            ->setMethod('POST')
            ->setQuery([
                'mode' => 'file',

                'name' => null,
                'waitdir' => null,
            ])
            ->setResponseFilter([
                'filePath'
            ])
            ->setResponseFormater($fns[self::REQ_UPLOAD]);

        // Lister les vues de l'utilisateur: query/gettreearchive
        $services[ self::REQ_GET_PERIMETER ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('query/gettreearchive')
            ->setMethod('GET')
            ->setResponseFilter([])
            ->setResponseFormater( $fns[self::REQ_GET_PERIMETER] );

        // Afficher les résultats d'une vue: query/gettreearchive
        $services[ self::REQ_EXEC_REQUEST ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('query/getexec')
            ->setMethod('GET')
            ->setQuery([
                'qryid' => '',
                'limitstart' => null,
                'limitgridlines' => null,

                'qryusrffqn' => null,
                'qryusrop' => null,
                'qryusrval' => null,
            ])
            ->setResponseFilter([])
            ->setResponseFormater( $fns[self::REQ_EXEC_REQUEST] );

        // Voir la liste des fichiers (image) d'un enregistrement
        $services[ self::REQ_GET_RECORD_FILES ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('docpak/loadalllastrevision')
            ->setMethod('GET')
            ->setQuery([
                'docpakrsid' => 0,
                'docpaktbl' => '',
                'docpakpage' => '*',
                'fsfileinfo' => 1,

                'limitstart' => null,
                'limitgridlines' => null,
            ])
            ->setResponseFilter([
                'rank',
                'rsid',
                'table',
                'fsfileid',
                'ripefilearchive',
                'datefilearchive',

                'mime',
                'namefileorigin',
                'size',
            ])
            ->setResponseFormater( $fns[self::REQ_GET_RECORD_FILES] );

        // Créer un enregistrement
        $services[ self::REQ_CREATE_RECORD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('doctbl/insertrow')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'fields' => '',
                'values' => '',

                'qryid' => null,
            ])
            ->setResponseFilter([])
            ->setResponseFormater( $fns[self::REQ_CREATE_RECORD] );

        // Mettre à jour un enregistrement
        $services[ self::REQ_UPDATE_RECORD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('doctbl/updaterow')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'field_ID' => 0,
                'value_ID' => 0,
                'fields' => '',
                'values' => '',
            ])
            ->setResponseFilter([]);

        /**
         * Ajouter un fichier (image) à jour un enregistrement
         *
         * paramètres :
         *  - tfqn : Le nom de la table.
         *  - rsid : L'ID de la fiche (ici 118).
         *  - file : Le chemin, sur le serveur, du fichier.
         *  - start : -1
         *  - ocr : 1 pour garder le format d'origine, 0 pour archiver seulement le fichier converti (selon le format).
         */
        $services[ self::REQ_ADD_RECORD_FILE ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('docpak/addpages')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'rsid' => 0,
                'file' => '',
                'ocr' => 1,
                'start' => -1,
            ])
            ->setResponseFilter([
                'JOBQUEUE_ID'
            ])
            ->setResponseFormater( $fns[self::REQ_ADD_RECORD_FILE] );

        /**
         * Connaitre le status d'un JOB
         *
         * paramètres :
         *  - jobqueueid : l' ID du job.
         */
        $services[ self::REQ_GET_JOB_STATUS ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('jobqueue/load')
            ->setMethod('GET')
            ->setQuery([
                'jobqueueid' => 0,
            ])
            ->setResponseFilter([])
            ->setResponseFormater( $fns[self::REQ_GET_JOB_STATUS] );


        return $services;
    }

}
