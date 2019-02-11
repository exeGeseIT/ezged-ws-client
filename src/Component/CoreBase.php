<?php

/*
 * The MIT License
 *
 * Copyright 2019 Jean-Claude GLOMBARD <jc.glombard@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace JcgDev\EzGEDWsClient\Component;

use JcgDev\EzGEDWsClient\Component\ServiceConfig;

/**
 * Description of CoreBase
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class CoreBase
{

    const ERRORCODE_OK = 0;
    
    /**
     * Ouvrir une session (Authent)
     */
    const REQ_AUTH = 'sec/authenticate';

    /**
     * Maintenir la connexion
     */
    const REQ_AUTH_KEEPALIVE = 'secses/keepalive';

    /**
     * Fermer une session (Logout)
     */
    const REQ_LOGOUT = 'secses/delete';

    /**
     * Upload d'un Fichier
     */
    const REQ_UPLOAD = 'upload';

    /**
     * Lister les vues/requêtes disponibles pour le compte utilisé
     */
    const REQ_GET_PERIMETER = 'query/gettreearchive';

    /**
     * Obtenir toutes les lignes retournées par une requête.
     */
    const REQ_EXEC_REQUEST = 'query/getexec';

    /**
     * Voir la liste des fichiers (image) d'un enregistrement
     */
    const REQ_GET_RECORD_FILES = 'docpak/loadalllastrevision';

    /**
     * Créer un enregistrement
     */
    const REQ_CREATE_RECORD = 'doctbl/insertrow';

    /**
     * Mettre à jour un enregistrement
     */
    const REQ_UPDATE_RECORD = 'doctbl/updaterow';

    /**
     * Ajouter un fichier (image) à jour un enregistrement
     */
    const REQ_ADD_RECORD_FILE = 'docpak/addpages';

    /**
     * Connaitre le status d'un job
     */
    const REQ_GET_JOB_STATUS = 'jobqueue/load';




    /**
     *
     * @param string $serviceKey
     * @return ServiceConfig
     */
    abstract protected function getServiceConfig( string $serviceKey );


    /**
     *
     * @return array
     */
    protected static function initServices() {
        
        $services = [];

        // Authent: sec/authenticate
        $services[ self::REQ_AUTH ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('sec/authenticate')
            ->setMethod('GET')
            ->setQuery([
                'login' => '',
                'pwd' => '',
            ])
            ->setResponseFilter([
                'sessionid',
            ]);

        // KeepAlive: secses/keepalive
        $services[ self::REQ_AUTH_KEEPALIVE ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('secses/keepalive')
            ->setMethod('GET')
            ->setResponseFilter([
                'countsignbook',
                'countcorrection',
                'counttrash',
                'countmessage',
                'countworkflow',
            ]);

        // Logout: secses/delete
        $services[ self::REQ_LOGOUT ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('secses/delete')
            ->setMethod('GET')
            ->setQuery([
                'sessionid' => '',
                'secsesid' => '',
            ])
            ->setResponseFilter([]);

        // Upload d'un Fichier
        $services[ self::REQ_UPLOAD ] = (new ServiceConfig())
            ->setEndpoint('pupload.php')
            ->setMethod('POST')
            ->setQuery([
                'mode' => 'file',

                'name' => null,
                'waitdir' => null,
            ])
            ->setResponseFilter([
                'filePath'
            ]);

        // Lister les vues de l'utilisateur: query/gettreearchive
        $services[ self::REQ_GET_PERIMETER ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('query/gettreearchive')
            ->setMethod('GET')
            ->setResponseFilter([]);

        // Afficher les résultats d'une vue: query/gettreearchive
        $services[ self::REQ_EXEC_REQUEST ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('query/getexec')
            ->setMethod('GET')
            ->setQuery([
                'qryid' => '',
                'limitstart' => null,
                'limitgridlines' => null,

                'qryusrffqn' => null,
                'qryusrop' => null,
                'qryusrval' => null,
            ])
            ->setResponseFilter([]);

        // Voir la liste des fichiers (image) d'un enregistrement
        $services[ self::REQ_GET_RECORD_FILES ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('docpak/loadalllastrevision')
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
                'rsid',
                'table',
                'fsfileid',
                'ripefilearchive',

                'mime',
                'namefileorigin',
                'size',
            ]);

        // Créer un enregistrement
        $services[ self::REQ_CREATE_RECORD ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('doctbl/insertrow')
            ->setMethod('POST')
            ->setQuery([
                'tfqn' => '',
                'fields' => '',
                'values' => '',

                'qryid' => null,
            ])
            ->setResponseFilter([]);

        // Mettre à jour un enregistrement
        $services[ self::REQ_UPDATE_RECORD ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('doctbl/updaterow')
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
        $services[ self::REQ_ADD_RECORD_FILE ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('docpak/addpages')
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
            ]);

        /**
         * Connaitre le status d'un JOB
         *
         * paramètres :
         *  - jobqueueid : l' ID du job.
         */
        $services[ self::REQ_GET_JOB_STATUS ] = (new ServiceConfig())
            ->setEndpoint('service.php')
            ->setService('jobqueue/load')
            ->setMethod('GET')
            ->setQuery([
                'jobqueueid' => 0,
            ])
            ->setResponseFilter([]);


        return $services;
    }

    
}
