<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGED extends EzGEDAbstract
{
    private HttpClientInterface $httpclient;
    
    /**
     * 
     * @param HttpClientInterface $httpclient
     */
    public function __construct(HttpClientInterface $httpclient)
    {
        $this->httpclient = $httpclient;
        parent::__construct();
    }
    
    
    public function exec(string $serviceKey, array $queryParams = [], array $options = []): EzGEDResponseInterface
    {
        // Manage "Token" authentification mode
        if ( !isset($queryParams['token'], $queryParams['sessionid']) ) {
            unset($queryParams['token']);
            unset($queryParams['sessionid']);
        }
        
        $conf = $this->getServiceConfig($serviceKey);
        
        $response = $this->httpclient->request($conf->getMethod(), $conf->getEndpoint(), array_merge([
            'query' => $conf->getQueryParameters($queryParams),
        ], $options));
        
        return $conf->getReturn($response);

    }
    
}