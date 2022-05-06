<?php

namespace ExeGeseIT\EzGEDWsClient\Exception;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzGEDClientExceptionInterface extends \Throwable 
{
    /**
     * Return the initial exception classname
     * @return string|null
     */
    public function getThrowed(): ?string;

}
