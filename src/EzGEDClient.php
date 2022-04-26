<?php

namespace ExeGeseIT\EzGEDWsClient;

use ExeGeseIT\EzGEDWsClient\Core\EzGED;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseInterface;
use ExeGeseIT\EzGEDWsClient\Core\Response\ConnectResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\CreateRecordResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\EmptyResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\JobstatusResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\PerimeterResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\RecordPageResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\SearchResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\UploadResponse;
use ExeGeseIT\EzGEDWsClient\Exception\AuthenticationException;
use ExeGeseIT\EzGEDWsClient\Exception\EzGEDClientException;
use ExeGeseIT\EzGEDWsClient\Exception\LogoutException;
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
     * @param ?string $apiDomain
     * @return self
     */
    public function setApiDomain(?string $apiDomain): self
    {
        $apiDomain = empty($apiDomain) ? null : trim($apiDomain);
        $this->sessionid = ($apiDomain === $this->apiDomain) ?: null;
        $this->apiDomain = $apiDomain;
        return $this;
    }
    
    /**
     * @param string $apiUser
     * @return self
     */
    public function setApiUser(string $apiUser): self
    {
        $this->sessionid = ($apiUser === $this->apiUser) ?: null;
        $this->apiUser = $apiUser;
        return $this;
    }
    
    /**
     * @param string $apiPwd
     * @return self
     */
    public function setApiPwd(string $apiPwd): self
    {
        $this->sessionid = ($apiPwd === $this->apiPwd) ?: null;
        $this->apiPwd = $apiPwd;
        $this->sessionid = null;
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
     * @param string $ezgedUrl
     * @param HttpClientInterface|null $httpclient
     * @param string|null $apiUser
     * @param string|null $apiPwd
     * @param string|null $apiDomain
     * @param bool|null $sslVerifyPeer (default: true)
     */
    public function __construct(string $ezgedUrl, ?HttpClientInterface $httpclient = null, ?string $apiUser = null, ?string $apiPwd = null, ?string $apiDomain = null, ?bool $sslVerifyPeer = true)
    {
        $this->setApiDomain( $apiDomain );
        $this->setApiUser($apiUser ?? \time());
        $this->setApiPwd($apiPwd ?? '');
        $this->apiUrl = Path::canonicalize( ($ezgedUrl ?? '/ezged')) . '/data/';
        $this->setSslVerifyPeer($sslVerifyPeer ?? true);
        
        $this->filesystem = new Filesystem();
        
        $this->ezGED = new EzGED($httpclient ?? HttpClient::create(), $this->apiUrl);
    }
    
    private function getParams(array $params = []): array
    {
        return array_merge($params, [
            'format' => 'json',
            'sessionid' => $this->sessionid ?? '',
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
            'domain' => $this->apiDomain,
            'login' => $this->apiUser,
            'pwd' => $this->getApiPwd(),
        ];
        
        /** @var ConnectResponse $ezResponse */
        $ezResponse = $this->ezGED->exec(EzGED::REQ_AUTH, $params, $this->getOptions());
        
        if ( !$ezResponse->isSucceed() ) {
            throw new AuthenticationException($ezResponse->getMessage(), -1);
        }
        
        $this->sessionid = $ezResponse->getSessionid();
        $this->cookie = $ezResponse->getHttpHeaders()['set-cookie'];

        if ( $withKeepalive ) {
            if ( $this->ezGED->exec(EzGED::REQ_AUTH_KEEPALIVE, $this->getParams($params), $this->getOptions())->isSucceed() ) {
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

        $response = $this->authent()->ezGED->getHttpresponse(EzGED::REQ_DOWNLOAD_FILE, $this->getParams($params), $this->getOptions());
        
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

        return $this->authent()->ezGED->getHttpresponse(EzGED::REQ_CREATE_RECORD, $this->getParams($params), $this->getOptions());
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
        
        return $this->authent()->ezGED->getHttpresponse(EzGED::REQ_UPDATE_RECORD, $this->getParams($params), $this->getOptions());
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

        return $this->authent()->ezGED->exec(EzGED::REQ_UPLOAD, $this->getParams($params), $this->getOptions($options));
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

        return $this->authent()->ezGED->exec(EzGED::REQ_ADD_RECORD_FILE, $this->getParams($params), $this->getOptions($options));
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
            'jobqueueid' => $idjob,
        ];

        /** @var JobstatusResponse $response */
        $response = $this->authent()->ezGED->exec(EzGED::REQ_GET_JOB_STATUS, $this->getParams($params), $this->getOptions($options));

        if ( $waitFinalState && $response->isSucceed() && !$response->onFinalState() ) {

            $retry = (60 / EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME);
            $wait = true;
            while ( $wait ) {
                $this->logger && $this->logger->info( sprintf(' > waiting %ds before next job status request.', EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME));
                sleep( EzGEDHelper::DEFAULT_JOBSTATUS_POOLING_WAITTIME );
                
                $response = $this->authent()->ezGED->exec(EzGED::REQ_GET_JOB_STATUS, $this->getParams($params), $this->getOptions($options));
                $retry--;

                $wait = $retry && !$response->onFinalState();
            }
        }

        return $response;
    }
    
}
