<?php

namespace ExeGeseIT\EzGEDWsClient;

use ExeGeseIT\EzGEDWsClient\Core\EzGED;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDError;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseInterface;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDServicesInterface;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDSessionManagerInterface;
use ExeGeseIT\EzGEDWsClient\Core\Response\ConnectResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\CreateRecordResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\EmptyResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\JobstatusResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\PerimeterResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\RecordPageResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\SearchResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\UploadResponse;
use ExeGeseIT\EzGEDWsClient\Core\SessionManager\ProxySesionManager;
use ExeGeseIT\EzGEDWsClient\Exception\AuthenticationException;
use ExeGeseIT\EzGEDWsClient\Exception\EzGEDClientException;
use ExeGeseIT\EzGEDWsClient\Exception\LogoutException;
use ExeGeseIT\EzGEDWsClient\Exception\MaxSessionReachedException;
use Psr\Log\LoggerInterface;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Description of EzGEDClient
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDClient
{
    private string $apiUrl;
    private ?string $apiDomain = null;
    private string $apiUser = '';
    private string $apiPwd = '';
    private bool $sslVerifyPeer = true;
    
    private Filesystem $filesystem;
    
    private bool $keepalive = false;
    private ?array $cookie = null;
    
    private EzGED $ezGED;
    private EzGEDSessionManagerInterface $sessionManager;
    
    private ?LoggerInterface $logger = null;
    
    /**
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
    
    /**
     * @param string|null $msg
     * @param string|null $level
     * @return void
     */
    private function log(?string $msg, ?string $level = null): void
    {
        $this->logger && $this->logger->log($level ?? 'info', $msg);
    }

    /**
     * @param EzGEDSessionManagerInterface|null $sessionManager
     * @return self
     */
    public function setSessionManager(?EzGEDSessionManagerInterface $sessionManager): self
    {
        $this->sessionManager = $sessionManager;
        $this->resetManagerToken();
        return $this;
    }
        
    /**
     * @param bool|null $state
     * @return self
     */
    public function setPersistent(?bool $state=true): self
    {
        $this->keepalive = $state;
        return $this;
    }
    
    /**
     * @param ?string $apiDomain
     * @return self
     */
    public function setApiDomain(?string $apiDomain): self
    {
        $apiDomain = empty($apiDomain) ? null : trim($apiDomain);
        if ($apiDomain !== $this->apiDomain) {
            $this->setSessionid(null);
        }
        $this->apiDomain = $apiDomain;
        $this->resetManagerToken();
        return $this;
    }
    
    /**
     * @param string $apiUser
     * @return self
     */
    public function setApiUser(string $apiUser): self
    {
        if ($apiUser !== $this->apiUser) {
            $this->setSessionid(null);
        }
        $this->apiUser = $apiUser;
        $this->resetManagerToken();
        return $this;
    }
    
    /**
     * @param string $apiPwd
     * @return self
     */
    public function setApiPwd(string $apiPwd): self
    {
        if ($apiPwd !== $this->apiPwd) {
            $this->setSessionid(null);
        }
        $this->apiPwd = $apiPwd;
        $this->resetManagerToken();
        return $this;
    }
    
    /**
     * @return string
     */
    public function getApiPwd(): string
    {
        //In "Domaine" context, pwd should be send clearly :/
        return $this->apiDomain ? $this->apiPwd : md5($this->apiPwd);
    }
    
    /**
     * @param bool $sslVerifyPeer (default: true)
     * @return self
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer = true): self
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
        return $this;
    }
    
    /**
     * Reset EzGEDSessionManagerInterfce token
     */
    private function resetManagerToken(): void
    {
        if ( null !== $this->sessionManager ) {
            $token = sprintf('%s::%s::%s' 
                , $this->apiDomain ?? ''
                , $this->apiUser ?? ''
                , $this->apiPwd ?? ''
            );
            $this->sessionManager->setToken( sha1($token) );
        }
    }
    
    /**
     * @return string|null
     */
    public function getSessionid(): ?string
    {
        return $this->sessionManager->getIdSession();
    }

    /**
     * @param string|null $sessionid
     * @return void
     */
    private function setSessionid(?string $sessionid): void
    {
        $this->sessionManager->setIdSession($sessionid);
    }

    
            
    
    /**
     * @param string $ezgedUrl
     * @param HttpClientInterface|null $httpclient
     * @param string|null $apiUser
     * @param string|null $apiPwd
     * @param string|null $apiDomain
     * @param bool|null $sslVerifyPeer (default: true)
     */
    public function __construct(string $ezgedUrl, ?HttpClientInterface $httpclient = null, ?string $apiUser = null, ?string $apiPwd = null, ?string $apiDomain = null, ?bool $sslVerifyPeer = true)
    {
        $this->sessionManager = new ProxySesionManager();
        
        $this->setApiDomain( $apiDomain );
        $this->setApiUser($apiUser ?? \time());
        $this->setApiPwd($apiPwd ?? '');
        $this->apiUrl = Path::canonicalize( ($ezgedUrl ?? '/ezged')) . '/data/';
        $this->setSslVerifyPeer($sslVerifyPeer ?? true);
        
        
        $this->ezGED = new EzGED($httpclient ?? HttpClient::create(), $this->apiUrl);
    }
    
    private function getParams(array $params = []): array
    {
        return array_merge($params, [
            'format' => 'json',
            'sessionid' => $this->getSessionid() ?? '',
        ]);
    }
    
    private function getOptions(array $options = []): array
    {
        if ( array_key_exists('headers', $options) && is_array($options['headers']) ) {
            $options[ 'headers' ] = array_merge($options['headers'], ['Cookie' => implode(',', $this->cookie ?? [])]);
        } else {
            $options[ 'headers' ] = [
                'Cookie' => implode(',', $this->cookie ?? []),
            ];
        }
        
        return array_merge($options, [
            'verify_peer' => $this->sslVerifyPeer,
            'base_uri' => $this->apiUrl,
        ]);
    }
    
    /**
     * Check if the managed session is still valid
     * @return bool
     */
    private function isDeadSession(): bool
    {
        /** @var EzGEDResponseInterface $response */
        $response = $this->ezGED->exec(EzGEDServicesInterface::REQ_GET_JOB_STATUS, $this->getParams(['jobqueueid'=>0]), $this->getOptions());
        if ( $isdead = EzGEDError::NO_VALID_SESSION == $response->getErrorNumber() ) {
            $this->log(sprintf(' > %s[%d]: %s', basename(__METHOD__), $response->getErrorNumber(), $response->getMessage()), 'debug');
            $this->logout();
        }
        return $isdead;
    }
    
    /**
     * @return self
     * @throws AuthenticationException
     */
    private function authent(): self
    {
        if ( !$this->getSessionid() || $this->isDeadSession() ) {
            $this->connect();
        }
        return $this;
    }
    
    
    /**
     * Auto kill not persistent session (keepalive == false)
     * 
     * @param EzGEDResponseInterface|null $response
     * @return EzGEDResponseInterface|null
     */
    private function resolve(?EzGEDResponseInterface $response): ?EzGEDResponseInterface
    {
        if ( !$this->keepalive ) {
            $this->logout();
        }
        return $response;
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
            'domain' => $this->apiDomain,
            'login' => $this->apiUser,
            'pwd' => $this->getApiPwd(),
        ];
        
        $this->keepalive = $this->keepalive || $withKeepalive;
        
        /** @var ConnectResponse $ezResponse */
        $ezResponse = $this->ezGED->exec(EzGEDServicesInterface::REQ_AUTH, $params, $this->getOptions());
        
        if ( !$ezResponse->isSucceed() ) {
            switch ($ezResponse->getErrorNumber()) {
                case EzGEDError::MAX_SESSION_REACHED:
                    $this->log(sprintf('EzGEDClient::connect() failed with: [%d]:%s', $ezResponse->getErrorNumber(), $ezResponse->getMessage()), 'debug');
                    throw new MaxSessionReachedException($ezResponse->getMessage(), -1);
                    break;
                default:
                    $this->log(sprintf('EzGEDClient::connect() failed with: [%d]:%s', $ezResponse->getErrorNumber(), $ezResponse->getMessage()), 'debug');
                    throw new AuthenticationException($ezResponse->getMessage(), -1);
                    break;
            }
        }
        
        $this->setSessionid( $ezResponse->getSessionid() );
        $this->cookie = $ezResponse->getHttpHeaders()['set-cookie'];

        if ( $this->keepalive ) {
            $state = $this->ezGED->exec(EzGEDServicesInterface::REQ_AUTH_KEEPALIVE, $this->getParams($params), $this->getOptions())->isSucceed();
            $this->log( sprintf(' > Turning EzGED session@%s on keepAlive state %s', $this->getSessionid(), ($state ? 'SUCCEEDED' : 'FAILED')) );
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
        $_sessionid = $this->getSessionid();
        $params = [
            'sessionid' => $_sessionid,
            'secsesid' => $_sessionid,
        ];

        /** @var EzGEDResponseInterface $ezResponse */
        $ezResponse = $this->ezGED->exec(EzGEDServicesInterface::REQ_LOGOUT, $this->getParams($params), $this->getOptions());
        if ( !$ezResponse->isSucceed() ) {
            throw new LogoutException($ezResponse->getMessage(), $ezResponse->getMessage());
        }
        
        $this->log( sprintf(' > EzGED session@%s CLOSED', $_sessionid) );
        
        $this->setSessionid(null);
        $this->cookie = null;

        return $this;
    }
    
    
    /**
     * List user views 
     * @return PerimeterResponse
     */
    public function getPerimeter(): PerimeterResponse
    {
        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_GET_PERIMETER, $this->getParams(), $this->getOptions());
        return $this->resolve( $response );
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
      * @param array|null $filters filters of the form [['field'=>, 'operator'=> 'value'=>], ...]
      * @param array|null $sorts order of the form [['field'=>, 'direction'=> ], ...]
      * @return SearchResponse
      */
    public function search(int $idview, ?int $offset = null, ?int $limit = null, ?array $filters = null, ?array $sorts = null): SearchResponse
    {
        $params = [
            'qryid' => $idview,
            'limitstart' => $offset ?? 0,
            'limitgridlines' => $limit,
        ];

        // SORTING
        if ( !empty($sorts) ) {
            $_sort = [
                'qryusrorderfld' => [],
                'qryusrorderdirection' => [],
            ];
            
            $isArrayOfArrays = $sorts === array_filter($sorts, 'is_array');
            $sorts = $isArrayOfArrays ? $sorts : [ $sorts ];
            foreach ($sorts as $sort) {
                $isKeyOk = isset($sort['field'], $sort['direction']);
                $direction = isset($sort['direction']) ? strtoupper($sort['direction']) : '-!!-';
                
                if ($isKeyOk && EzGEDHelper::isSearchDirection($direction)) {
                    $_sort['qryusrorderfld'][] = $sort['field'];
                    $_sort['qryusrorderdirection'][] = $direction;
                }
            }

            if ( count($_sort['qryusrorderfld']) ) {
                $params['qryusrorderfld'] = sprintf('["%s"]', implode('","', $_sort['qryusrorderfld']));
                $params['qryusrorderdirection'] = sprintf('["%s"]', implode('","', $_sort['qryusrorderdirection']));
            }
        }

        // FILTERING
        if ( !empty($filters) ) {
            $_flt = [
                'qryusrffqn' => [],
                'qryusrop' => [],
                'qryusrval' => [],
            ];
            
            $isArrayOfArrays = $filters === array_filter($filters, 'is_array');
            $filters = $isArrayOfArrays ? $filters : [ $filters ];
            foreach ($filters as $filter) {
                $isKeyOk = isset($filter['field'], $filter['operator'], $filter['value']);
                $operator = isset($filter['operator']) ? strtolower($filter['operator']) : '-!!-';
                
                if ($isKeyOk && EzGEDHelper::isSearchOperator($operator)) {
                    $_flt['qryusrffqn'][] = $filter['field'];
                    $_flt['qryusrop'][] = $operator;
                    $_flt['qryusrval'][] = $filter['value'];
                }
            }

            if ( count($_flt['qryusrffqn']) ) {
                $params['qryusrffqn'] = sprintf('["%s"]', implode('","', $_flt['qryusrffqn']));
                $params['qryusrop'] = sprintf('["%s"]', implode('","', $_flt['qryusrop']));
                $params['qryusrval'] = sprintf('["%s"]', implode('","', $_flt['qryusrval']));
            }
        }

        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_EXEC_REQUEST, $this->getParams($params), $this->getOptions());
        return $this->resolve( $response );
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

        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_GET_RECORD_FILES, $this->getParams($params), $this->getOptions());
        return $this->resolve( $response );
    }
    
    
    /**
     * 
     * @param int $idfile
     * @param string $fileHash
     * @param string $saveFilepath
     * @param string|null $saveFilename
     * @return array ['file' => saved_file_name, 'size' => size(octet)]
     * 
     * @throws EzGEDClientException
     */
    public function downloadFile(int $idfile, string $fileHash, string $saveFilepath, ?string $saveFilename = null): array
    {
        $params = [
            'fsfileid' => $idfile,
            'fsfileripe' => $fileHash,
            'mobile' => 1,
        ];

        $response = $this->authent()->ezGED->getHttpresponse(EzGEDServicesInterface::REQ_DOWNLOAD_FILE, $this->getParams($params), $this->getOptions());
        
        if ( 200 !== $response->getStatusCode() ) {
            throw new EzGEDClientException('Request failed with status code: '.$response->getStatusCode(), $response->getStatusCode());
        }
        
        if ( 'application/force-download' !== $response->getInfo('content_type') ) {
            throw new EzGEDClientException('Unable to retrieve the requested archive. The specified parameters are invalid or incomplete');
        }
        
        if ( empty($saveFilename) ) {
            $content_disposition_header = implode(' ',$response->getHeaders()['content-disposition']);
            $saveFilename = preg_replace('/.*filename="(.+)"/', '$1', $content_disposition_header);
        }
        
        $output = Path::canonicalize($saveFilepath . '/' . $saveFilename);
        $path = Path::getDirectory($output);
        
        if ( null === $this->filesystem ) {
            $this->filesystem = new Filesystem();
        }
        $this->filesystem->mkdir($path);
        
        $size = 0;
        $file = new SplFileObject($output, 'w');
        foreach ($this->ezGED->stream($response) as $chunk) {
            $size += $file->fwrite($chunk->getContent());
        }
        
        return [
            'path' => $path, 
            'filename' => $file->getFilename(), 
            'size' => $size,
        ];
        
    }
    
    
    
    
    /* ************************ */
    /* CREATE/UPDATE OPERATIONS */
    /* ************************ */
    
    /**
     * Create a new Record
     *
     * @param string $recordTable
     * @param array $fields
     * @param int|null $qryid (default: null)
     * @return CreateRecordResponse
     */
    public function createRecord(string $recordTable, array $fields, ?int $qryid = null): CreateRecordResponse
    {
        $params = [
            'tfqn' => $recordTable,
            'fields' => \json_encode(array_keys($fields)),
            'values' => \json_encode(array_values($fields)),
            'qryid' => $qryid,
        ];

        return $this->authent()->ezGED->getHttpresponse(EzGEDServicesInterface::REQ_CREATE_RECORD, $this->getParams($params), $this->getOptions());
    }
    
    
    /**
     * Update a Record
     *
     * @param int $idrecord
     * @param string $recordTable
     * @param string $primaryField
     * @param array $fields
     * @return EmptyResponse
     */
    public function updateRecord(int $idrecord, string $recordTable, string $primaryField, array $fields): EmptyResponse
    {
        $params = [
            'tfqn' => $recordTable,
            'field_ID' => $primaryField,
            'value_ID' => $idrecord,

            'fields' => \json_encode(array_keys($fields)),
            'values' => \json_encode(array_values($fields)),
        ];
        
        return $this->authent()->ezGED->getHttpresponse(EzGEDServicesInterface::REQ_UPDATE_RECORD, $this->getParams($params), $this->getOptions());
    }
    
    
    /**
     * Upload a file
     *
     * Optional parameters:
     *    - $name: =>    The name that will be indexed for the file (GED)
     *                   default: basename($fullFilename)
     *    - $waitdir: => The name of the COLD waiting directory in which the files will be written
     *                   The directory must be in the ocr/wait tree (e.g: C:\nchp\var\spool\ezged\instance\ocr\wait)
     *    - $token: =>   This parameter MUST BE LEFT at its default value unless EzGED changes the value defined for <upload_token>
     *                   default: EzGEDHelper::DEFAULT_UPLOAD_TOKEN
     *
     * @param string $fullFilename Full file name (e.g: c:/test/fact-5678.pdf)
     * @param string|null $name
     * @param string|null $waitdir
     * @param string|null $token
     * @return UploadResponse
     *
     * @throws EzGEDClientException
     */
    public function upload(string $fullFilename, ?string $name = null, ?string $waitdir = null, ?string $token = null): UploadResponse
    {

        $filename = Path::canonicalize($fullFilename);
        $params = [
            'token' => $token ?? EzGEDHelper::DEFAULT_UPLOAD_TOKEN, 
            'waitdir' => $waitdir,
            'mode' => empty($waitdir) ? null : 'cold',
            
            'name' => $name ?? basename($filename), 
        ];
        
        if ( !is_readable($filename) ) {
            throw new EzGEDClientException( sprintf('Unable to read %s . Check existence and  permission', $filename) );
        }
        
        $formData = new FormDataPart([
            'file' => DataPart::fromPath($filename),
        ]);
        
        $headers = [];
        foreach ($formData->getPreparedHeaders()->toArray() as $string) {
            $matches = [];
            if ( preg_match('/^(?P<header>.+):(?P<value>.+)$/', $string, $matches) ) {
                $headers[ $matches['header'] ] = trim($matches['value']);
            }
        }
        
        $options = [
            'headers' => $headers,
            'body' => $formData->bodyToIterable(),
        ];
        
        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_UPLOAD, $this->getParams($params), $this->getOptions($options));
        return $this->resolve( $response );
    }
    
    
    /**
     * Add a file (Page) to a record
     *
     * @param int $idrecord
     * @param string $recordTable
     * @param string $serverFilePath The server path to the file.
     * @param bool $convertBeforeArchive FALSE to keep original format, TRUE to archive only converted file (depending on format)
     * @return JobstatusResponse
     */
    public function addRecordPage(int $idrecord, string $recordTable, string $serverFilePath, bool $convertBeforeArchive = false): JobstatusResponse
    {
        $params = [
            'tfqn' => $recordTable,
            'rsid' => $idrecord,
            'file' => $serverFilePath,
            'ocr' => ($convertBeforeArchive ? 0 : 1),
        ];

        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_ADD_RECORD_FILE, $this->getParams($params), $this->getOptions($options));
        return $this->resolve( $response );
    }
    
    /**
     *
     * @param int $jobId
     * @param bool $waitFinalState  if TRUE ==> waiting until job pass on 'final State' status (default: false)
     * @return JobstatusResponse
     */
    public function getJobStatus(int $jobId, bool $waitFinalState = false): JobstatusResponse
    {
        $params = [
            'jobqueueid' => $jobId,
        ];

        /** @var JobstatusResponse $response */
        $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_GET_JOB_STATUS, $this->getParams($params), $this->getOptions($options));

        if ( $waitFinalState && $response->isSucceed() && !$response->onFinalState() ) {

            $retry = (60 / EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME);
            $wait = true;
            while ( $wait ) {
                $this->log( sprintf(' > waiting %ds before next job status request.', EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME) );
                sleep( EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME );
                
                $response = $this->authent()->ezGED->exec(EzGEDServicesInterface::REQ_GET_JOB_STATUS, $this->getParams($params), $this->getOptions($options));
                $retry--;

                $wait = $retry && !$response->onFinalState();
            }
        }

        return $this->resolve( $response );
    }
    
}
