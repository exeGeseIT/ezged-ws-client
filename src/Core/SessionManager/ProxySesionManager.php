<?php

namespace ExeGeseIT\EzGEDWsClient\Core\SessionManager;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDSessionManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Description of ProxySesionManager
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class ProxySesionManager implements EzGEDSessionManagerInterface
{
    private $idsession;
    private $logger;
    
    /**
     * @param string|null $msg
     * @return void
     */
    private function log(?string $msg): void
    {
        $this->logger && $this->logger->debug($msg);
    }
    
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $this->logger;
    }
    
    /**
     * {@inheritDoc}
     */
    public function setToken(string $token): void
    {}

    /**
     * {@inheritDoc}
     */
    public function getIdSession(): ?string
    {
        return $this->idsession;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdSession(?string $idsession): ?string
    {
        $this->idsession = $idsession;
        return $this->idsession;
    }

}
