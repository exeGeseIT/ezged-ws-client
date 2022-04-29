<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzBagInterface
{
    public function init(iterable $rawData): self;
    public function getId(): ?int;
    
    /**
     * @return array|bool|string|int|float|null
     * @throws ParameterNotFoundException if the parameter is not defined
     */
    public function getProperty(string $name);
    
    /**
     * @param string $name
     * @param type $value
     * @return self
     */
    public function setProperty(string $name, $value): self;
    
    /**
     * Return the "EzGED" name of $property or null if $property is not defined
     * @param string $property
     * @return string|null
     */
    public function getPropertyName(string $name): ?string;
    
    /**
     * @return array
     */
    public function getElements(): array;
    
}
