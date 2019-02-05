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

/**
 * Description of CoreBase
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class CoreBase
{

    const STATUSCODE_OK = 0;
    
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
    const REQ_VIEW_DOCPAK = 'docpak/loadalllastrevision';



    protected $confServices;


    protected function _initConfServices() {
        
        $this->confServices = [];

        // Authent: sec/authenticate
        $this->confServices[ self::REQ_AUTH ] = (new ServiceConfig())
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
        $this->confServices[ self::REQ_AUTH_KEEPALIVE ] = (new ServiceConfig())
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
        $this->confServices[ self::REQ_LOGOUT ] = (new ServiceConfig())
                ->setEndpoint('service.php')
                ->setService('secses/delete')
                ->setMethod('GET')
                ->setQuery([
                    'sessionid' => '',
                    'secsesid' => '',
                ])
                ->setResponseFilter([]);

        // Upload d'un Fichier
        $this->confServices[ self::REQ_UPLOAD ] = (new ServiceConfig())
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
        $this->confServices[ self::REQ_GET_PERIMETER ] = (new ServiceConfig())
                ->setEndpoint('service.php')
                ->setService('query/gettreearchive')
                ->setMethod('GET')
                ->setResponseFilter([]);

        // Afficher les résultats d'une vue: query/gettreearchive
        $this->confServices[ self::REQ_EXEC_REQUEST ] = (new ServiceConfig())
                ->setEndpoint('service.php')
                ->setService('query/getexec')
                ->setMethod('GET')
                ->setQuery([
                    'qryid' => '',
                    'limitstart' => 0,
                    'limitgridlines' => 20,

                    'qryusrffqn' => null,
                    'qryusrop' => null,
                    'qryusrval' => null,
                ])
                ->setResponseFilter([]);

        // Voir la liste des fichiers (image) d'un enregistrement
        $this->confServices[ self::REQ_VIEW_DOCPAK ] = (new ServiceConfig())
                ->setEndpoint('service.php')
                ->setService('docpak/loadalllastrevision')
                ->setMethod('GET')
                ->setQuery([
                    'docpakrsid ' => 0,
                    'docpaktbl  ' => '',
                    'docpakpage' => '*',
                    'fsfileinfo ' => 1,
                ])
                ->setResponseFilter([
                    'rsid',
                    'fsfileid',
                    'ripefilearchive',
                ]);

    }

    /**
     *
     * @param string $serviceKey
     * @return ServiceConfig
     */
    abstract protected function getServiceConfig( string $serviceKey );
    

}
