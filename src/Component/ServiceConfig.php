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

namespace ExeGeseIT\EzGEDWsClient\Component;

/**
 * Description of ServiceConfig
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class ServiceConfig
{
    private $service;
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
        $this->service = null;
        $this->endpoint = '';
        $this->method = '';
        $this->query = [];
        $this->responseFilter = null;
        $this->responseFormater = null;
    }


    /**
     *
     * @param array $params
     * @return array
     */
    public function buildRequestQuery(array $params)
    {
        $reqQuery = [];

        if (null !== $this->getService()) {
            $reqQuery['service'] = $this->service;
        }

        unset($params['service']);
        $reqQuery = array_merge($reqQuery,$this->getQuery());

        foreach ($params as $key => $value) {
            if (array_key_exists($key, $reqQuery)) {
                if (is_array($value)) {
                    $reqQuery = array_merge($reqQuery,$value);
                } else {
                    $reqQuery[ $key ] = $value;
                }
            }
        }

        //dump(['conf' => $this->getQuery(), 'params' => $params, 'buildRequestQuery' => $reqQuery]);

        return $reqQuery;
    }



    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function setEndpoint($endpoint = '')
    {
        $this->endpoint = trim($endpoint);
        return $this;
    }

    public function setMethod($methodh = '')
    {
        $this->method = trim($methodh);
        return $this;
    }

    public function setQuery(array $query)
    {
        $this->query = $query;
        return $this;
    }

    public function setResponseFilter(array $filter)
    {
        $this->responseFilter = $filter;
        return $this;
    }

    public function setResponseFormater(callable $fn)
    {
        $this->responseFormater = $fn;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getMethod()
    {
        return ( empty($this->method) ? 'GET' : $this->method);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getResponseFilter()
    {
        return $this->responseFilter;
    }

    public function getResponseFormater()
    {
        return $this->responseFormater;
    }

}
