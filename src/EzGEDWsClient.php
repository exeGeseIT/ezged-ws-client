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

    public function getResponse() {
        return $this->requester->getResponse();
    }
    

    /**
     * S'assure qu'on a bien une session ouverte
     */
    private function _connect() {
        if ( null === $this->sessionid ) {
            $this->connect();
        }
    }

    
    /**
     *
     * @return $this
     */
    public function connect () {
        if ( null === $this->sessionid ) {
            $_params = [
                'login' => $this->apiUser,
                'pwd' => $this->apiPwd,
            ];
            $this->requester->exec(Core::REQ_AUTH, $_params);

            if ( Core::STATUSCODE_OK === $this->getErrorCode() ) {
                $r = $this->getResponse();
                $this->sessionid = $r[0]->sessionid;
            }
        }
        return $this;
    }

    /**
     * 
     * @return $this
     */
    public function logout () {
        if ( null !== $this->sessionid ) {
            $_params = [
                'sessionid' => $this->sessionid,
                'secsesid' => $this->sessionid,
            ];
            $this->requester->exec(Core::REQ_LOGOUT, $_params);

            if ( Core::STATUSCODE_OK === $this->getErrorCode() ) {
                $this->sessionid = null;
            }
        }
        return $this;
    }


    /**
     *  Lister les vues de l'utilisateur
     * @return $this
     */
    public function getPerimeter () {
        $this->_connect();
        $this->requester->exec(Core::REQ_GET_PERIMETER);
        return $this;
    }

    /**
     *  Afficher les résultats d'une vue
     *
     * le paramètre $filter permet de filtrer la recherche
     * Il doit être de la forme:
     * [
     *   'field'    => Nom du champ de la base de donnée sur lequel rechercher
     *   'operator' => operateur: '=' | '>=' | '<=' | 'like'
     *   'value'    => Valeur à rechercher
     * ]
     *
     *
     * @param int $idview   identifiant de la vue (QRY_ID)
     * @param int $offset   offset pour la pagination du résultat
     * @param int $limit    nombre de ligne de résulta retourné
     * @param array|null $filter  filtre de la forme ['field'=>, 'operator'=> 'value'=>]
     * @return $this
     */
    public function requestView ( $idview, $offset = 0, $limit = 20, array $filter = null ) {

        $_params = [
            'qryid' => $idview,
            'limitstart' => $offset,
            'limitgridlines' => $limit,
        ];

        if ( !empty($filter) ) {
            $isKeyOk = (array_key_exists('field',$filter) && array_key_exists('operator',$filter) && array_key_exists('value',$filter) );
            $operator = array_key_exists('operator',$filter) ? strtolower($filter['operator']) : '--';
            $isOperatorOK = in_array($operator,['=', '>=', '<=', 'like']);

            if ( $isKeyOk && $isOperatorOK ) {
                $_params['qryusrffqn'] = $filter['field'];
                $_params['qryusrop'] = $operator;
                $_params['qryusrval'] = $filter['value'];
            }
        }

        $this->_connect();
        $this->requester->exec(Core::REQ_REQUEST_VIEW,$_params);

        return $this;
    }

}
