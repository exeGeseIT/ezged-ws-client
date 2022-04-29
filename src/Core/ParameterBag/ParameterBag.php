<?php

namespace ExeGeseIT\EzGEDWsClient\Core\ParameterBag;

use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\Exception\ParameterNotFoundException;
use function str_contains;

/**
 * This Class is wildly inspired by the class of the same name from symfony/dependency-injection project, 
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class ParameterBag implements ParameterBagInterface
{
    protected $parameters = [];

    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->add($key, $value);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function add(string $name, $value): self
    {
        $this->parameters[ $name ] = is_array($value) ? new self($value) : $value;
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->parameters;
    }
    
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        if (!\array_key_exists($name, $this->parameters)) {
            if (!$name) {
                throw new ParameterNotFoundException($name);
            }

            $alternatives = [];
            foreach (array_keys($this->parameters) as $key) {
                $lev = levenshtein($name, $key);
                if ($lev <= \strlen($name) / 3 || str_contains($key, $name)) {
                    $alternatives[] = $key;
                }
            }

            $nonNestedAlternative = null;
            if (!\count($alternatives) && str_contains($name, '.')) {
                $namePartsLength = array_map('strlen', explode('.', $name));
                $key = substr($name, 0, -1 * (1 + array_pop($namePartsLength)));
                while (\count($namePartsLength)) {
                    if ($this->has($key)) {
                        if (\is_array($this->get($key))) {
                            $nonNestedAlternative = $key;
                        }
                        break;
                    }

                    $key = substr($key, 0, -1 * (1 + array_pop($namePartsLength)));
                }
            }

            throw new ParameterNotFoundException(sprintf('%s (%s)',$name,$name), $alternatives, $nonNestedAlternative);
        }

        return $this->parameters[$name];
    }

}
