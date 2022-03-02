<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDServiceConfigurator;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDServicesInterface;
use ExeGeseIT\EzGEDWsClient\Core\Response\ConnectResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\CreateRecordResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\EmptyResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\JobstatusResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\KeepaliveResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\PerimeterResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\RecordPageResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\SearchResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\UploadResponse;
use ExeGeseIT\EzGEDWsClient\EzGEDHelper;

/**
 * Description of Services
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzGEDAbstract implements EzGEDServicesInterface
{

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
        self::$services = [];
        
        // Authent: sec/authenticate
        self::$services[ self::REQ_AUTH ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('sec/authenticate')
            ->setMethod('GET')
            ->setQuery([
                'login' => '',
                'pwd' => '',
            ])
            ->setReturnClass(ConnectResponse::class)
            ;

        // KeepAlive: secses/keepalive
        self::$services[ self::REQ_AUTH_KEEPALIVE ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('secses/keepalive')
            ->setMethod('POST')
            ->setReturnClass(KeepaliveResponse::class)
            ;

        // Logout: secses/delete
        self::$services[ self::REQ_LOGOUT ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('secses/delete')
            ->setMethod('GET')
            ->setQuery([
                'sessionid' => '',
                'secsesid' => '',
            ])
            ;

        // Récupérer un fichier
        self::$services[ self::REQ_DOWNLOAD_FILE ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('showdocs.php')
            ->setMethod('GET')
            ->setQuery([
                'fsfileid' => 0,
                'fsfileripe' => 'n0p',

                'mode' => null,
                'mobile' => null, //mobile =1 force 'download'
            ])
            ;

        // Upload d'un Fichier
        self::$services[ self::REQ_UPLOAD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('pupload.php')
            ->setMethod('POST')
            ->setQuery([
                'token' => null, 
                'sessionid' => null,
                
                'mode' => 'file',
                'waitdir' => null,
                
                'name' => null,
            ])
            ->setReturnClass(UploadResponse::class)
            ;

        // Lister les vues de l'utilisateur: query/gettreearchive
        self::$services[ self::REQ_GET_PERIMETER ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('query/gettreearchive')
            ->setMethod('GET')
            ->setReturnClass(PerimeterResponse::class)
            ;

        // Afficher les résultats d'une vue: query/gettreearchive
        self::$services[ self::REQ_EXEC_REQUEST ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('query/getexec')
            ->setMethod('GET')
            ->setQuery([
                'qryid' => '',
                'limitstart' => null,
                'limitgridlines' => EzGEDHelper::DEFAULT_SEARCH_LIMIT,

                'qryusrffqn' => null,
                'qryusrop' => null,
                'qryusrval' => null,
            ])
            ->setReturnClass(SearchResponse::class)
            ;

        // Voir la liste des fichiers (image) d'un enregistrement
        self::$services[ self::REQ_GET_RECORD_FILES ] = (new EzGEDServiceConfigurator())
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
            ->setReturnClass(RecordPageResponse::class)
            ;

        // Créer un enregistrement
        self::$services[ self::REQ_CREATE_RECORD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('doctbl/insertrow')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'fields' => '',
                'values' => '',
                'qryid' => null,
            ])
            ->setReturnClass(CreateRecordResponse::class)
            ;

        // Mettre à jour un enregistrement
        self::$services[ self::REQ_UPDATE_RECORD ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('doctbl/updaterow')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'field_ID' => '',
                'value_ID' => 0,
                'fields' => '',
                'values' => '',
            ])
            ->setReturnClass(EmptyResponse::class)
            ;

        /**
         * Ajouter un fichier (image) à un enregistrement
         *
         * paramètres :
         *  - tfqn : Le nom de la table.
         *  - rsid : L'ID de la fiche (ici 118).
         *  - file : Le chemin, sur le serveur, du fichier.
         *  - ocr : 1 pour garder le format d'origine, 0 pour archiver seulement le fichier converti (selon le format).
         *  - start : -1
         */
        self::$services[ self::REQ_ADD_RECORD_FILE ] = (new EzGEDServiceConfigurator())
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
            ->setReturnClass(JobstatusResponse::class)
            ;

        /**
         * Connaitre le status d'un JOB
         *
         * paramètres :
         *  - jobqueueid : l' ID du job.
         */
        self::$services[ self::REQ_GET_JOB_STATUS ] = (new EzGEDServiceConfigurator())
            ->setEndpoint('service.php')
            ->setServicename('jobqueue/load')
            ->setMethod('GET')
            ->setQuery([
                'jobqueueid' => 0,
            ])
            ->setReturnClass(JobstatusResponse::class)
            ;

    }

}
