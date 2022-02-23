<?php

namespace ExeGeseIT\EzGEDWsClient;

use ExeGeseIT\EzGEDWsClient\Core\EzGED;
use ExeGeseIT\EzGEDWsClient\Core\Response\ConnectResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\KeepaliveResponse;
use ExeGeseIT\EzGEDWsClient\Core\Response\PerimeterResponse;
use ExeGeseIT\EzGEDWsClient\Exception\AuthenticationException;
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
    
    private ?string $sessionid = null;
    private bool $keepalive = false;
    
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
            'base_uri' => rtrim($ezgedUrl ?? '','/') . '/data/',
            'query' => [
                'format' => 'json',
                'session' => $this->sessionid ?? '',
            ],
        ]);
        $this->ezGED = new EzGED($finalHttpclient);
    }
    
    
    public function connect(bool $withKeepalive = false): self
    {
        if (null === $this->sessionid) {
            $_params = [
                'login' => $this->apiUser,
                'pwd' => $this->apiPwd,
            ];
            
            /** @var ConnectResponse $ezResponse */
            $ezResponse = $this->ezGED->exec(EzGED::REQ_AUTH, $_params);

            if ( $ezResponse->isSucceed() ) {
                $this->sessionid = $ezResponse->getSessionid();
            } else {
                throw new AuthenticationException($ezResponse->getMessage(), $ezResponse->getMessage());
            }
        }

        if ( $withKeepalive && !$this->isKeepalive() ) {
            /** @var KeepaliveResponse $ezResponse */
            $ezResponse = $this->ezGED->exec(EzGED::REQ_AUTH_KEEPALIVE);
            if ( $ezResponse->isSucceed() ) {
                $this->keepalive = true;
            }
            $this->logger && $this->logger->debug( sprintf(' > Turn EzGED session@%s on keepAlive state: %s', $this->sessionid, ($this->isKeepalive() ? 'SUCCEED' : 'FAILED')));
        }
        return $this;
    }
    
    
    /**
     * Lister les vues de l'utilisateur
     * @return PerimeterResponse
     */
    public function getPerimeter(): PerimeterResponse
    {
        return $this->connect()->ezGED->exec(EzGED::REQ_GET_PERIMETER);
    }
    
    
}
