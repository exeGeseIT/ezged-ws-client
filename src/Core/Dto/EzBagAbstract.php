<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;


/**
 * Description of ezBag
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzBagAbstract implements EzBagInterface
{

    protected array $_properties = [];
    protected array $_callables = [];
    protected array $elements = [];

    /**
     * @var object|array
     */
    protected $data;


    public function __construct()
    {}

    public function setProperties(iterable $properties): self
    {
        foreach ($properties as $key) {
            $this->_properties[ strtolower($key) ] = null;
        }
        return $this;
    }

    public function addMethod(string $methodName, callable $fn): self
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
            /*$_name = strtolower(substr($methodName,3));
            if (array_key_exists($_name,$this->_properties)) {
                return $this->_properties[$_name];
            }*/
            return $this->__get( substr($methodName,3) );
        }
    }
    
    protected static function isSet(string $prop, $item): bool
    {
        if ( is_array($item) ) {
            return array_key_exists($prop, $item);
        }
        return is_object($prop) ? property_exists($item, $prop) : false;
    }
    
    protected static function extract(string $prop, $item)
    {
        if ( is_array($item) ) {
            return $item[ $prop ];
        }
        return is_object($prop) ? $item->$prop : null;
    }

    /**
     *
     * @param object $data
     * @param iterable $requiredProperties
     * @return bool
     */
    protected function validateData($data, iterable $requiredProperties = []): bool
    {
        $isOK = true;
        foreach ($requiredProperties as $prop) {
            $isOK = $isOK && self::isSet($prop, $data);
        }
        $this->data = $isOK ? $data : null;
        return $isOK;
    }

    protected function setProperty(string $property, $value): self
    {
        $this->_properties[ strtolower($property) ] = $value;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getData()
    {
        return $this->data;
    }

    
    /**
     * Function d'initialisation
     * @return EzBagInterface
     */
    abstract function init($arg): EzBagInterface;
}
