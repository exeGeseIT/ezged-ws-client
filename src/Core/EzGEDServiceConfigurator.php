<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use ExeGeseIT\EzGEDWsClient\Core\Response\EmptyResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Description of ServiceConfig
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDServiceConfigurator
{
    private $servicename;
    private $endpoint;
    private $method;
    private $query;
    private $returnClass;

    /**
     * 
     * @var array|null
     */
    private $responseFilter;

    /**
     *
     * @var callable|null
     */
    private $responseFormater;

    public function __construct()
    {
        $this->servicename = null;
        $this->endpoint = '';
        $this->method = null;
        $this->query = [];
        $this->responseFilter = null;
        $this->responseFormater = null;
    }


    /**
     *
     * @param array $params
     * @return array
     */
    public function getQueryParameters(array $params = []): array
    {
        unset($params['service']);
        $reqQuery = $this->getQuery();

        foreach ($params as $key => $value) {
            if ( array_key_exists($key, $reqQuery) ) {
                if ( is_array($value) ) {
                    $reqQuery = array_merge($reqQuery,$value);
                } else {
                    $reqQuery[ $key ] = $value;
                }
            }
        }
        return $reqQuery;
    }


    public function getServicename(): ?string
    {
        return $this->servicename;
    }

    public function setServicename(string $servicename): self
    {
        $this->servicename = $servicename;
        return $this;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint = ''): self
    {
        $this->endpoint = trim($endpoint);
        return $this;
    }
    
    public function getMethod(): string
    {
        return $this->method ?? 'GET';
    }

    public function setMethod(string $methodh): self
    {
        $this->method = trim($methodh);
        return $this;
    }

    public function getQuery(): array
    {
        $_q = empty($this->getServicename()) ? [] : ['service' => $this->getServicename()];
        return array_merge($this->query, $_q);
    }

    public function setQuery(array $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function getResponseFilter(): ?iterable
    {
        return $this->responseFilter;
    }
    
    public function setResponseFilter(iterable $filter): self
    {
        $this->responseFilter = $filter;
        return $this;
    }

    public function getResponseFormater(): ?callable
    {
        return $this->responseFormater;
    }
    
    public function setResponseFormater(callable $fn): self
    {
        $this->responseFormater = $fn;
        return $this;
    }
    
    /**
     * @param ResponseInterface $httpresponse
     * @return EzGEDResponseInterface
     */
    public function getReturn(ResponseInterface $httpresponse): EzGEDResponseInterface
    {
        $className = $this->returnClass;
        if( null === $className ) {
            new EmptyResponse($httpresponse);
        }
        return new $className($httpresponse);
    }

    public function setReturnClass(string $returnClass): self
    {
        $this->returnClass = $returnClass;
        return $this;
    }



}
