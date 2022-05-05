<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzGEDSessionManagerInterface
{
    /**
     * Provide an 'uniq token' to identified apiUser'
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void;
    
    /**
     * Reset the sessionid
     * @return void
     */
    public function resetSession(): void;
    
    /**
     * @return string|null
     */
    public function getIdSession(): ?string;
    
    /**
     * @param string|null $idsession
     * @return string|null
     */
    public function setIdSession(?string $idsession): ?string;
}
