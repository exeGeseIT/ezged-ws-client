<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;

/**
 * Description of KeepaliveResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class KeepaliveResponse extends EzGEDResponseAbstract
{
    private ?int $countsignbook = null;
    private ?int $countcorrection = null;
    private ?int $counttrash = null;
    private ?int $countmessage = null;
    private ?int $countworkflow = null;
    
    public function getCountsignbook(): ?int
    {
        return $this->countsignbook;
    }
    
    public function getCountcorrection(): ?int
    {
        return $this->countcorrection;
    }

    public function getCounttrash(): ?int
    {
        return $this->counttrash;
    }

    public function getCountmessage(): ?int
    {
        return $this->countmessage;
    }

    public function getCountworkflow(): ?int
    {
        return $this->countworkflow;
    }

    protected function initialize(array $data): void
    {
        $rows = array_key_exists('rows', $data) ? $data['rows'][0] : null;
        if ( $rows ) {
            foreach ([
                'countsignbook',
                'countcorrection',
                'counttrash',
                'countmessage',
                'countworkflow',
            ] as $property) {
                $this->$property = $rows[ $property ];
            }
        }
    }

}
