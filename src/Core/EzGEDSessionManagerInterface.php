<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzGEDSessionManagerInterfce
{
    /**
     * Provide an 'uniq token' to identified apiUser'
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void;
    
    /**
     * @param string|null $idsession
     * @return void
     */
    public function setIdSesion(?string $idsession): void;
    
    /**
     * @return string|null
     */
    public function getIdSesion(): ?string;
}
