<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;


/**
 * Description of ezBag
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzBagAbstract implements EzBagInterface
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
