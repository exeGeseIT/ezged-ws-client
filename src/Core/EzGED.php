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
    
    
    public function exec(string $serviceKey, array $params = [], array $options = []): EzGEDResponseInterface
    {
        $conf = $this->getServiceConfig($serviceKey);

        //$this->formater = $conf->getResponseFormater();

        $response = $this->httpclient->request($conf->getMethod(), $conf->getEndpoint(), array_merge([
            'query' => $conf->getQueryParameters($params),
        ], $options));
        
        return $conf->getReturn($response);

    }
    
}