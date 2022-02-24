<?php

namespace ExeGeseIT\EzGEDWsClient\Core\ParameterBag\Exception;

/**
 * This Class is wildly inspired by the class of the same name from symfony/dependency-injection project, 
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class ParameterNotFoundException extends \InvalidArgumentException
{
    private $key;
    private $alternatives;
    private $nonNestedAlternative;

    /**
     * @param string          $key                  The requested parameter key
     * @param string[]        $alternatives         Some parameter name alternatives
     * @param string|null     $nonNestedAlternative The alternative parameter name when the user expected dot notation for nested parameters
     */
    public function __construct(string $key, array $alternatives = [], string $nonNestedAlternative = null)
    {
        $this->key = $key;
        $this->alternatives = $alternatives;
        $this->nonNestedAlternative = $nonNestedAlternative;

        parent::__construct();

        $this->message = sprintf('You have requested a non-existent parameter "%s".', $this->key);

        if ($this->alternatives) {
            if (1 == \count($this->alternatives)) {
                $this->message .= ' Did you mean this: "';
            } else {
                $this->message .= ' Did you mean one of these: "';
            }
            $this->message .= implode('", "', $this->alternatives).'"?';
        } elseif (null !== $this->nonNestedAlternative) {
            $this->message .= ' You cannot access nested array items, do you want to inject "'.$this->nonNestedAlternative.'" instead?';
        }
    }

    public function getKey()
    {
        return $this->key;
    }

}