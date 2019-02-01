<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace JcgDev\EzGEDWsClient;

use JcgDev\EzGEDWsClient\Component\Core;

/**
 * Description of Client
 *
 * @author jc.glombard@gmail.com
 */
class EzGEDWsClient
{
    /**
     *
     * @var Core 
     */
    private $requester;

    private $apiUser;
    private $apiPwd;

    private $errorCode;
    private $errorMessage;

    private $sessionid;

    /**
     *
     * @param string $ezgedUrl  ex:http://localhost/ezged3
     * @param string $apiUser
     * @param string $apiPwd
     * @param null|ressource $httpRequestTraceHandler
     */
    public function __construct(string $ezgedUrl, string $apiUser, string $apiPwd, $httpRequestTraceHandler = null)
    {
        $this->apiUser = $apiUser;
        $this->apiPwd = md5($apiPwd);

        $this->sessionid = null;

        $this->requester = new Core($ezgedUrl,$httpRequestTraceHandler);

    }

    /**
     * On s'assure de bien fermer la session 
     */
    public function __destruct()
    {
        $this->logout();
    }


    public function getSessionId() {
        return $this->sessionid;
    }

    public function getRequestStatusCode() {
        return $this->requester->getStatusCode();
    }

    public function getRequestStatusMessage() {
        return $this->requester->getStatusMsg();
    }

    public function getErrorCode() {
        return $this->requester->getErrorCode();
    }

    public function getErrorMessage() {
        return $this->requester->getErrorMessage();
    }

    public function getRawJsonResponse() {
        return $this->requester->getRawJsonResponse();
    }


    /**
     * S'assure qu'on a bien une session ouverte
     */
    private function _connect() {
        if ( null === $this->sessionid ) {
            $this->connect();
        }
    }

    

    public function connect () {
        if ( null === $this->sessionid ) {
            $_params = [
                'login' => $this->apiUser,
                'pwd' => $this->apiPwd,
            ];
            $response = $this->requester->exec(Core::REQ_AUTH, $_params);

            if ( Core::STATUSCODE_OK === $this->getErrorCode() ) {
                $this->sessionid = $response[0]->sessionid;
            }
        }

        return $this;
    }

    public function logout () {
        
        if ( null !== $this->sessionid ) {
            $_params = [
                'sessionid' => $this->sessionid,
                'secsesid' => $this->sessionid,
            ];
            $response = $this->requester->exec(Core::REQ_LOGOUT, $_params);

            if ( Core::STATUSCODE_OK === $this->getErrorCode() ) {
                $this->sessionid = null;
            }
        }
        return $this;
    }


    /**
     *  Lister les vues de l'utilisateur
     * @return type
     */
    public function getPerimeter () {

        $this->_connect();
        $response = $this->requester->exec(Core::REQ_GET_PERIMETER);
        return $response;
    }

    /**
     *  Afficher les résultats d'une vue
     *
     * @param int $idview   identifiant de la vue (QRY_ID)
     * @param int $offset   offset pour la pagination du résultat
     * @param int $limit    nombre de ligne de résulta retourné
     * @return type
     */
    public function requestView ( $idview, $offset = 0, $limit = 20 ) {

        $_params = [
            'qryid' => $idview,
            'limitstart' => $offset,
            'limitgridlines' => $limit,
        ];

        $this->_connect();
        $response = $this->requester->exec(Core::REQ_REQUEST_VIEW,$_params);

        return $response;
    }

}
