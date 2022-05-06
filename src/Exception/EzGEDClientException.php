<?php

namespace ExeGeseIT\EzGEDWsClient\Exception;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGEDClientException extends \Exception implements EzGEDClientExceptionInterface 
{
    private ?string $throwedClassname = null;
    
    /**
     * Return the initial exception classname
     * @return string|null
     */
    public function getThrowed(): ?string
    {
        return $this->throwedClassname;
    }

    /**
     * 
     * @param null|string|\Exception $e
     */
    public function __construct($e = null, int $code = 0, ?\Throwable $previous = null)
    {
        if ($e instanceof \Exception ) {
            $this->throwedClassname = get_class($e);
            parent::__construct($e->getMessage(), $code ?: $e->getCode(), $e->getPrevious());
            
        } else {
            parent::__construct($e ?? '', $code, $previous);
        }
    }
}
