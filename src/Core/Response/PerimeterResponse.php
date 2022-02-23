<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzFamily;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;

/**
 * Description of PerimeterResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class PerimeterResponse extends EzGEDResponseAbstract
{
    protected function initializeData(array $data): void
    {
        $rows = array_key_exists('rows', $data) ? $data['rows'][0]['rows'] : null;
        if ( $rows ) {
            $out = [];
            foreach ($rows as $item) {
                $ezFamily = (new EzFamily())->init($item);
                $out[ $ezFamily->getId() ] = $ezFamily;
            }
            $this->data = $out;
        }
    }
}
