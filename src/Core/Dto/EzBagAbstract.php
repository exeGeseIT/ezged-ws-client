<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\Exception\ParameterNotFoundException;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;


/**
 * Description of ezBag
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzBagAbstract implements EzBagInterface
{

    protected ParameterBag $propertiesBag;
    protected array $elements = [];

    
    
    /**
     * Function d'initialisation
     * @param iterable $rawData
     * @return EzBagInterface
     */
    abstract function init(iterable $rawData): EzBagInterface;
    
    
    
    public function __construct()
    {
        $this->propertiesBag = new ParameterBag();
    }

    public function setProperties(iterable $properties): self
    {
        foreach ($properties as $propertyName) {
            $this->setProperty($propertyName, null);
        }
        return $this;
    }
    
    /**
     * Gets property value.
     *
     * @return array|bool|string|int|float|null
     *
     * @throws ParameterNotFoundException if the parameter is not defined
     */
    public function getProperty(string $name)
    {
        return $this->propertiesBag->get( strtolower($name) );
    }
        
    /**
     * 
     * @param string $name
     * @param type $value
     * @return self
     */
    public function setProperty(string $name, $value): self
    {
        $this->propertiesBag->add(strtolower($name), $value);
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
        return $isOK;
    }
    
}
