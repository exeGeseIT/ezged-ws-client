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

use GuzzleHttp\Client as GuzzleHttpClient;
use JcgDev\EzGEDWsClient\Component\ServiceConfig;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of Core
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class Core
{
    const STATUSCODE_OK = 0;

    const REQ_AUTH = 'sec/authenticate';
    const REQ_AUTH_KEEPALIVE = 'secses/keepalive';
    const REQ_LOGOUT = 'secses/delete';
    const REQ_GET_PERIMETER = 'query/gettreearchive';
    const REQ_REQUEST_VIEW = 'query/getexec';


    private $guzzle;

    private $statusCode;
    private $statusMsg;
    private $rawJsonResponse;
    private $errorCode;
    private $errorMessage;

    private $confServices;


    private function initConfServices() {
        $confServices = [];

        // Authent: sec/authenticate
        $confServices[ self::REQ_AUTH ] = new ServiceConfig();
        $confServices[ self::REQ_AUTH ]
                ->setEndpoint('service.php')
                ->setService('sec/authenticate')
                ->setQuery([
                    'login' => '',
                    'pwd' => '',
                ])
                ->setResponseFilter([
                    'sessionid',
                ]);

        // KeepAlive: secses/keepalive
        $confServices[ self::REQ_AUTH_KEEPALIVE ] = new ServiceConfig();
        $confServices[ self::REQ_AUTH_KEEPALIVE ]
                ->setEndpoint('service.php')
                ->setService('secses/keepalive')
                ->setResponseFilter([
                    'countsignbook',
                    'countcorrection',
                    'counttrash',
                    'countmessage',
                    'countworkflow',
                ]);

        // Logout: secses/delete
        $confServices[ self::REQ_LOGOUT ] = new ServiceConfig();
        $confServices[ self::REQ_LOGOUT ]
                ->setEndpoint('service.php')
                ->setService('secses/delete?')
                ->setQuery([
                    'sessionid' => '',
                    'secsesid' => '',
                ])
                ->setResponseFilter([]);


        // Lister les vues de l'utilisateur: query/gettreearchive
        $confServices[ self::REQ_GET_PERIMETER ] = new ServiceConfig();
        $confServices[ self::REQ_GET_PERIMETER ]
                ->setEndpoint('service.php')
                ->setService('query/gettreearchive')
                ->setResponseFilter([]);

        // Afficher les résultats d'une vue: query/gettreearchive
        $confServices[ self::REQ_REQUEST_VIEW ] = new ServiceConfig();
        $confServices[ self::REQ_REQUEST_VIEW ]
                ->setEndpoint('service.php')
                ->setService('query/getexec')
                ->setQuery([
                    'qryid' => '',
                    'limitstart' => 0,
                    'limitgridlines' => 20,
                ])
                ->setResponseFilter([]);



        return $confServices;
    }

    /**
     *
     * @param string $serviceKey
     * @return ServiceConfig
     */
    private function getServiceConfig(string $serviceKey ) {
        return array_key_exists($serviceKey, $this->confServices) ? $this->confServices[$serviceKey] : null;
    }


    private function _stateFill( ResponseInterface $response ) {
        $this->statusCode = $response->getStatusCode();
        $this->statusMsg = $response->getReasonPhrase();
    }

    private function _stateReset() {
        $this->statusCode = null;
        $this->statusMsg = null;
        $this->rawJsonResponse = null;
        $this->errorCode = null;
        $this->errorMessage = null;
    }

    private function parseResponse ( ResponseInterface $response, array $filter) {

        if ( null === $filter ) {
            return $response->getBody();
        }
        
        $this->rawJsonResponse = \GuzzleHttp\json_decode( (string) $response->getBody() );

        $this->errorCode = $this->rawJsonResponse->errorcode;
        $this->errorMessage = $this->rawJsonResponse->errormsg;

        $rows = property_exists($this->rawJsonResponse,'rows') ? $this->rawJsonResponse->rows : [];

        if ( !is_array($rows) ) {
            $rows = [$rows];
        }

        if ( empty($rows) || empty($filter) ) {
            return $rows;
        }

        $filtered = [];
        foreach ($rows as $row) {
            $_f = [];
            foreach ($filter as $key) {
                if ( property_exists($row,$key) ) {
                    $_f[ $key ] = $row->$key;
                }
            }
            $filtered[] = (object)$_f;
        }

        return $filtered;

    }


    public function __construct(string $ezgedUrl)
    {
        $this->_stateReset();

        $this->confServices = $this->initConfServices();

        $this->guzzle = new GuzzleHttpClient([
            'base_uri' => rtrim($ezgedUrl,'/') . '/data/',
            'cookies' => true,
            'debug' => true,
        ]);
    }


    public function exec(string $serviceKey, array $params = []) {
        $sconf = $this->getServiceConfig($serviceKey);
        $_response = $this->guzzle->get( $sconf->getEndpoint(), [
            'query' => $sconf->buildRequestQuery($params),
            'decode_content' => true,
        ]);

        $this->_stateFill($_response);

        return $this->parseResponse($_response, $sconf->getResponseFilter());
    }

    
    public function getStatusCode() {
        return (int) $this->statusCode;
    }

    public function getStatusMsg() {
        return $this->statusMsg;
    }
    
    public function getErrorCode() {
        return (int) $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

    public function getRawJsonResponse() {
        return $this->rawJsonResponse;
    }

}
