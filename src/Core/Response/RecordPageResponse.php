<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzFile;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;

/**
 * Description of PerimeterResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class RecordPageResponse extends EzGEDResponseAbstract
{
    protected function initialize(array $data): void
    {
        $rows = array_key_exists('rows', $data) ? $data['rows'] : null;
        if ( $rows ) {
            $out = [];
            foreach ($rows as $item) {
                $ezBag = (new EzFile())->init($item);
                $out[ $ezBag->getProperty('rank') ] = $ezBag;
            }
            $this->content = new ParameterBag($out);
        }
    }
}
