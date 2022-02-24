<?php

namespace ExeGeseIT\EzGEDWsClient\Core\ParameterBag;

use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\Exception\ParameterNotFoundException;

/**
 * This Class is wildly inspired by the class of the same name from symfony/dependency-injection project, 
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface ParameterBagInterface
{
    /**
     * Adds parameters to the service container parameters.
     *
     * @param string $name The parameter name
     * @param array|bool|string|int|float|null $value The parameter value
     * @return self
     * 
     * @throws LogicException if the parameter cannot be added
     */
    public function add(string $name, $value): self;

    /**
     * Gets all bag data.
     *
     * @return array
     */
    public function all();

    /**
     * Gets a bag parameter.
     *
     * @return array|bool|string|int|float|null
     *
     * @throws ParameterNotFoundException if the parameter is not defined
     */
    public function get(string $name);


    /**
     * Returns true if a parameter name is defined.
     *
     * @return bool
     */
    public function has(string $name);
}
