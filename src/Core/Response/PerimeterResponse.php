<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzFamily;
use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBag;

/**
 * Description of PerimeterResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class PerimeterResponse extends EzGEDResponseAbstract
{
    protected function initialize(array $data): void
    {
        $rows = !empty($data['rows']) ? $data['rows'][0]['rows'] : null;
        if ( $rows ) {
            $out = [];
            foreach ($rows as $item) {
                $ezFamily = (new EzFamily())->init($item);
                $out[ $this->tokenize($ezFamily) ] = $ezFamily;
            }
            $this->content = new ParameterBag($out);
        }
    }
}
