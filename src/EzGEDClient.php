<?php

namespace ExeGeseIT\EzGEDWsClient;

use ExeGeseIT\EzGEDWsClient\Core\EzGED;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseInterface;
use ExeGeseIT\EzGEDWsClient\Core\Response\ConnectResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\PerimeterResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\RecordPageResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\SearchResponse;
use ExeGeseIT\EzGEDWsClient\Exception\AuthenticationException;
use ExeGeseIT\EzGEDWsClient\Exception\LogoutException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Description of EzGEDClient
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDClient
{
    private string $apiUser;
    private string $apiPwd;
    
    private bool $keepalive = false;
    private ?string $sessionid = null;
    private ?array $cookie = null;
    
    private EzGED $ezGED;
    
    private ?LoggerInterface $logger = null;
    
    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
    
    
    public function isKeepalive(): bool
    {
        return $this->sessionid && $this->keepalive;
    }

        
    
    /**
     * 
     * @param HttpClientInterface|null $httpclient
     * @param string $apiUser
     * @param string $apiPwd
     * @param string|null $ezgedUrl
     */
    public function __construct(?HttpClientInterface $httpclient, string $apiUser, string $apiPwd, ?string $ezgedUrl = null)
    {
        $this->apiUser = $apiUser;
        $this->apiPwd = md5($apiPwd);
        
        $finalHttpclient = $httpclient ?? HttpClient::create([
            'verify_peer' => false,
            'base_uri' => rtrim($ezgedUrl ?? '/ezged','/') . '/data/',
            'query' => [
                'format' => 'json',
            ],
        ]);
        $this->ezGED = new EzGED($finalHttpclient);
    }
    
    private function getParams(array $params = []): array
    {
        return array_merge($params, [
            'sessionid' => $this->sessionid ?? '',
        ]);
    }
    
    private function getOptions(): array
    {
        return [
            'headers' => [
                'Cookie' => implode(',', $this->cookie ?? []),
            ],
        ];
    }
    
    /**
     * 
     * @param bool $withKeepalive
     * @return self
     * @throws AuthenticationException
     */
    private function authent(bool $withKeepalive = false): self
    {
        if ( !$this->sessionid ) {
            $this->connect($withKeepalive);
        }
        return $this;
    }
    
    
    /**
     * 
     * @param bool $withKeepalive
     * @return ConnectResponse
     * @throws AuthenticationException
     */
    public function connect(bool $withKeepalive = false): ConnectResponse
    {
        $params = [
            'login' => $this->apiUser,
            'pwd' => $this->apiPwd,
        ];

        /** @var ConnectResponse $ezResponse */
        $ezResponse = $this->ezGED->exec(EzGED::REQ_AUTH, $params);

        if ( !$ezResponse->isSucceed() ) {
            throw new AuthenticationException($ezResponse->getMessage(), $ezResponse->getMessage());
        }
        
        $this->sessionid = $ezResponse->getSessionid();
        $this->cookie = $ezResponse->getHttpHeaders()['set-cookie'];

        if ( $withKeepalive ) {
            if ( $this->ezGED->exec(EzGED::REQ_AUTH_KEEPALIVE, $this->getParams(), $this->getOptions())->isSucceed() ) {
                $this->keepalive = true;
            }
            $this->logger && $this->logger->debug( sprintf(' > Turn EzGED session@%s on keepAlive state: %s', $this->sessionid, ($this->isKeepalive() ? 'SUCCEED' : 'FAILED')));
        }
        return $ezResponse;
    }
    
    
    /**
     * 
     * @return self
     * @throws LogoutException
     */
    public function logout(): self
    {
        $params = [
            'sessionid' => $this->sessionid,
            'secsesid' => $this->sessionid,
        ];

        /** @var EzGEDResponseInterface $ezResponse */
        $ezResponse = $this->ezGED->exec(EzGED::REQ_LOGOUT, $this->getParams($params), $this->getOptions());
        if ( !$ezResponse->isSucceed() ) {
            throw new LogoutException($ezResponse->getMessage(), $ezResponse->getMessage());
        }
        
        $this->logger && $this->logger->debug( sprintf(' > EzGED session@%s CLOSED', $this->sessionid) );
        
        $this->sessionid = null;
        $this->keepalive = true;
        $this->cookie = null;

        return $this;
    }
    
    
    /**
     * List user views 
     * @return PerimeterResponse
     */
    public function getPerimeter(): PerimeterResponse
    {
        return $this->authent()->ezGED->exec(EzGED::REQ_GET_PERIMETER, $this->getParams(), $this->getOptions());
    }
    
    /**
      * Show results of a view
      *
      * the $filter parameter allows you to filter the search
      * It must be of the form:
      * [
      *     'field' => Name of the database field to search on
      *     'operator' => operator: '=' | '>=' | '<=' | 'like'
      *     'value' => Value to search for
      * ]
      *
      *
      * @param int $idview view ID (QRY_ID)
      * @param int|null $offset offset for paging the result
      * @param int|null $limit number of result rows returned
      * @param array|null $filter filters of the form ['field'=>, 'operator'=> 'value'=>]
      * @return SearchResponse
      */
    public function search(int $idview, ?int $offset = null, ?int $limit = null, ?array $filter = null): SearchResponse
    {
        $params = [
            'qryid' => $idview,
            'limitstart' => $offset,
            'limitgridlines' => $limit,
        ];

        if ( !empty($filter) ) {
            $isKeyOk = isset($filter['field'], $filter['operator'], $filter['value']);
            $operator = isset($filter['operator']) ? strtolower($filter['operator']) : '-!!-';
            $isOperatorOK = in_array($operator,['=', '>=', '<=', 'like']);

            if ($isKeyOk && $isOperatorOK) {
                $params['qryusrffqn'] = $filter['field'];
                $params['qryusrop'] = $operator;
                $params['qryusrval'] = $filter['value'];
            }
        }

        return $this->authent()->ezGED->exec(EzGED::REQ_EXEC_REQUEST, $this->getParams($params), $this->getOptions());
    }
    
    
    /**
      * Returns the list of pages (file) of a record (row)
      *
      * @param int $idrecord identifier (PK) of the record (ie. 'NOTEDEFRAIS_ID')
      * @param string $recordTable record table name (ie. 'FEESNOTE')
      * @param int|null $offset offset for paging the result
      * @param int|null $limit number of result rows returned
      * @return RecordDocument
      */
    public function getRecordPages(int $idrecord, string $recordTable, ?int $offset = null, ?int $limit = null): RecordPageResponse
    {
        $params = [
            'docpakrsid' => $idrecord,
            'docpaktbl' => $recordTable,
            'limitstart' => $offset,
            'limitgridlines' => $limit,
        ];

        return $this->authent()->ezGED->exec(EzGED::REQ_GET_RECORD_FILES, $this->getParams($params), $this->getOptions());
    }
    
}
