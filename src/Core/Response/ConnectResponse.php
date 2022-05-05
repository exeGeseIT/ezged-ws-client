<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;

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
    
    protected function initialize(array $data): void
    {
        $rows = !empty($data['rows']) ? $data['rows'] : null;
        if ( $rows ) {
            $this->sessionid = $rows['sessionid'];
            unset($rows['sessionid']);
            
            if ( !empty($rows) ) {
                $this->content = new ParameterBag($rows);
            }
        }
    }

}
