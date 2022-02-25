<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGED extends EzGEDAbstract
{
    private HttpClientInterface $httpclient;
    private string $ezgedUrl;
    
    /**
     * 
     * @param HttpClientInterface $httpclient
     * @param string $ezgedUrl
     */
    public function __construct(HttpClientInterface $httpclient, string $ezgedUrl)
    {
        $this->httpclient = $httpclient;
        $this->ezgedUrl = $ezgedUrl;
        parent::__construct();
    }
    
    
    public function getHttpresponse(string $serviceKey, array $queryParams = [], array $options = []): ResponseInterface
    {
        // Manage "Token" authentification mode
        if ( !isset($queryParams['token'], $queryParams['sessionid']) ) {
            unset($queryParams['token']);
            unset($queryParams['sessionid']);
        }
        
        $conf = $this->getServiceConfig($serviceKey);
        
        return $this->httpclient->request($conf->getMethod(), $conf->getEndpoint(), array_merge([
            'query' => $conf->getQueryParameters($queryParams),
        ], $options));
    }
    
    
    public function exec(string $serviceKey, array $queryParams = [], array $options = []): EzGEDResponseInterface
    {
        $response = $this->getHttpresponse($serviceKey, $queryParams, $options);
        $conf = $this->getServiceConfig($serviceKey);
        return $conf->getReturn($response);
    }
    
    
    public function stream(ResponseInterface $responses): ResponseStreamInterface
    {
        return $this->httpclient->stream($responses);
    }
    
}