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
    
    private array $propertyMap = [];

    
    
    /**
     * Function d'initialisation
     * @param iterable $rawData
     * @return EzBagInterface
     */
    abstract public function init(iterable $rawData): EzBagInterface;
    
    
    
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
     * {@inheritdoc}
     */
    public function getProperty(string $name)
    {
        return $this->propertiesBag->get( strtolower($name) );
    }
        
    /**
     * {@inheritdoc}
     */
    public function setProperty(string $name, $value): self
    {
        $this->propertyMap[ strtolower($name) ] = $name;
        $this->propertiesBag->add(strtolower($name), $value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPropertyName(string $property): ?string
    {
        $name = strtolower($property);
        return isset($this->propertyMap[$name]) ? $this->propertyMap[$name] : null;
    }

    /**
     * {@inheritdoc}
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
