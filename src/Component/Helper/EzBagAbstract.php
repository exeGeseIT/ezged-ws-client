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

namespace JcgDev\EzGEDWsClient\Component\Helper;

/**
 * Description of ezBag
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzBagAbstract
{

    /**
     *
     * @var array 
     */
    protected $_properties;

    /**
     *
     * @var array
     */
    protected $_callables;

    /**
     *
     * @var array
     */
    protected $elements;

    /**
     *
     */
    protected $data;


    public function __construct()
    {
        $this->_properties = [];
        $this->_callables = [];
        $this->elements = [];
    }

    public function setProperties(iterable $properties)
    {
        foreach ($properties as $key) {
            $this->_properties[ strtolower($key) ] = null;
        }
        return $this;
    }

    public function addMethod(string $methodName, callable $fn)
    {
        $this->_callables[ $methodName ] = $fn;
        return $this;
    }

    public function __get(string $name)
    {
        $_name = strtolower($name);
        if (array_key_exists($_name,$this->_properties)) {
            return $this->_properties[$_name];
        }
    }

    public function __call(string $methodName, $args)
    {
        if (array_key_exists($methodName,$this->_callables)) {
            return call_user_func_array($this->_callables[$methodName],$args);
        } elseif (substr($methodName,0,3) === 'get') {
            $_name = strtolower(substr($methodName,3));
            if (array_key_exists($_name,$this->_properties)) {
                return $this->_properties[$_name];
            }
        }
    }

    /**
     *
     * @param object $data
     * @param array $requiredProperties
     * @return bool
     */
    protected function validateData($data, array $requiredProperties = [])
    {
        $isOK = true;
        foreach ($requiredProperties as $prop) {
            $isOK = $isOK && property_exists($data, $prop);
        }
        $this->data = $isOK ? $data : null;
        return $isOK;
    }

    protected function setProperty(string $property, $value)
    {
        $this->_properties[ strtolower($property) ] = $value;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    public function getData()
    {
        return $this->data;
    }

    
    /**
     * Function d'initialisation
     * @return $this
     */
    abstract function init($arg);
}
