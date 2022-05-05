<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzFile;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Description of PerimeterResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class CreateRecordResponse extends EzGEDResponseAbstract
{
    private ?int $recordId;
    
    public function getRecordId(): ?int
    {
        return $this->recordId;
    }
        
    protected function initialize(array $data): void
    {
        $rows = !empty($data['rows']) ? $data['rows'][0] : null;
        if ( $rows ) {
            $this->recordId = $rows['RETID'];
            unset($rows['RETID']);
            
            if ( !empty($rows) ) {
                $this->content = new ParameterBag($rows);
            }
        }
    }
}
