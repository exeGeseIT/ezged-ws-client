<?php

/*
 * The MIT License
 *
 * Copyright 2019 Jean-Claude GLOMBARD <jc.glombard@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ExeGeseIT\EzGEDWsClient\Core;

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
    public function buildRequestQuery(array $params = []): array
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

}
