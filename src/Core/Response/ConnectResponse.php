<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;

/**
 * Description of ConnectResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class ConnectResponse extends EzGEDResponseAbstract
{
    private string $sessionid;
    
    public function getSessionid(): ?string
    {
        return $this->sessionid;
    }
    
    protected function initializeData(array $data): void
    {
        $rows = array_key_exists('rows', $data) ? $data['rows'] : null;
        if ( $rows ) {
            $this->sessionid = $rows['sessionid'];
            unset($rows['sessionid']);
        }
        $this->data = $rows ?? [];
    }

}
