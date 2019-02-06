<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace JcgDev\EzGEDWsClient;

use GuzzleHttp\Psr7;
use JcgDev\EzGEDWsClient\Component\Core;

/**
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

    private $traceLogHandler;
    private $_called;
    private $_args;

    
    /**
     * On s'assure de bien fermer la session
     */
    public function __destruct()
    {
        $this->logout();

        /*if ( null !== $this->traceLogHandler ) {
            fclose($this->traceLogHandler);
        }*/
        $this->traceLogHandler = null;
    }

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

        $this->traceLogHandler = null;
        $this->_called = '';
        $this->_args = [];

    }


    private function _setTraceParam( string $calledMethod, array $param = []) {
        $this->_called = $calledMethod;
        $this->_args = $param;
        return $this;
    }


    /**
     *
     * @param string $traceLogFilename
     * @param string $mode
     * @return $this
     */
    public function setTraceLogHandler(string $traceLogFilename, string $mode = 'w') {
        $traceLogHandler = new \SplFileObject($traceLogFilename,$mode);
        if ( $traceLogHandler->isWritable() ) {
            $this->traceLogHandler = $traceLogHandler;
        }
        return $this;
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

    public function isSucceed() {
        return (Core::ERRORCODE_OK === $this->getResponse());
    }


    public function trace( $withRaw = false ) {

        if ( null !== $this->traceLogHandler ) {
            $reqKey = $this->_called;
            $log = [];
            $log[] = sprintf("---------- %s ( %s ) ----------", $reqKey, $this->getSessionId());
            if ( !empty($this->_args) ) {
                foreach ($this->_args as $key => $value) {
                    $_val = ( is_null($value) || is_scalar ($value) ) ? $value : json_encode($value);
                    $log[] =  sprintf("  ~ %s: %s",$key,$_val);
                }
                $log[] =  sprintf("---------- ", $reqKey);
            }
            $log[] =  sprintf("  STATUS >> [ %s ] - %s", $this->getRequestStatusCode(),$this->getRequestStatusMessage());
            $log[] =  sprintf("   ERROR >> [ %s ] - %s", $this->getErrorCode(),$this->getErrorMessage());
            if ( $withRaw ) {
                $log[] =  sprintf(" RAW >> %s", json_encode($this->getRawJsonResponse(),JSON_PRETTY_PRINT) );
            }
            $log[] =  sprintf("RESPONSE >> ", $reqKey);
            $log[] =  sprintf("%s ", json_encode($this->getResponse(),JSON_PRETTY_PRINT));
            $log[] =  sprintf("-------------------- ^ --------------------\n", $reqKey);
            $log[] = "\n";

            $this->traceLogHandler->fwrite( implode("\n",$log) );
        }

        return $this;
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
            $this->_setTraceParam(__METHOD__)
                 ->requester->exec(Core::REQ_AUTH, $_params);

            if ( $this->isSucceed() ) {
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
            $this->_setTraceParam(__METHOD__)
                 ->requester->exec(Core::REQ_LOGOUT, $_params);

            if ( $this->isSucceed() ) {
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
        $this->connect()
             ->_setTraceParam(__METHOD__)
             ->requester->exec(Core::REQ_GET_PERIMETER);
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
    public function requestView ( int $idview, int $offset = null, int $limit = null, array $filter = null ) {

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

        $this->connect()
             ->_setTraceParam(__METHOD__, ['$idview'=>$idview, '$offset'=>$offset, '$limit'=>$limit, '$filter'=>$filter])
             ->requester->exec(Core::REQ_EXEC_REQUEST,$_params);

        return $this;
    }

    /**
     * Tous les paramètres sont optionels
     * $params = [
     *   - 'name' => le nom qui sera indexé pour le fichier (GED)
     *                   par défaut: basename($fullFilename)
     *   - 'waitdir'  => Le nom du répertoire d'attente d'un COLD dans lequel le fichiers sera écrit
     *                   Le répertoire doit se trouver dans l'arborescence ocr/wait (e.g: C:\nchp\var\spool\ezged\instance\ocr\wait)
     * ]
     *
     *
     * @param string $fullFilename  Nom complet du fichier (ie: c:/test/fact-5678.pdf)
     * @param array $params
     * @return $this
     *
     */
    public function upload ( string $fullFilename, array $params = [] ) {

        $_params = array_merge(['name'=>basename($fullFilename), 'waitdir'=>null],$params);

        if ( !empty($_params['waitdir']) ) {
            $_params['mode'] = 'cold';
        }

        $resource = Psr7\try_fopen($fullFilename, 'r');

        $_options = [
            'multipart' => [
                [
                    'name' => 'file',
                    'filename' => $_params['name'],
                    'contents' => Psr7\stream_for($resource),
                ],
            ]
        ];

        $this->connect()
             ->_setTraceParam(__METHOD__, ['$fullFilename'=>$fullFilename, '$params'=>$params])
             ->requester->exec(Core::REQ_UPLOAD,$_params,$_options);
        return $this;
    }

    /**
     *  Retourne la liste des fichiers (image) d'un enregistrement (row)
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
     * @param int $idrecord   identifiant (PK) de l'enregistrement (ie. 'NOTEDEFRAIS_ID')
     * @param string $recordTable   nom de la table de l'enregistrement (ie. 'NOTEDEFRAIS')
     * @param int $offset   offset pour la pagination du résultat
     * @param int $limit    nombre de ligne de résulta retourné
     * @return $this
     */
    public function getRecordFiles ( int $idrecord, string $recordTable, int $offset = null, int $limit = null ) {

        $_params = [
            'docpakrsid' => $idrecord,
            'docpaktbl' => $recordTable,
            'limitstart' => $offset,
            'limitgridlines' => $limit,
        ];

        $this->connect()
             ->_setTraceParam(__METHOD__, ['$idrecord'=>$idrecord, '$recordTable'=>$recordTable, '$offset'=>$offset, '$limit'=>$limit])
             ->requester->exec(Core::REQ_GET_DOCPAK_FILES,$_params);

        return $this;
    }

}
