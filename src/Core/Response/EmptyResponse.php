<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;

/**
 * Description of EmptyResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EmptyResponse extends EzGEDResponseAbstract
{
    protected function initialize(array $data): void
    {}
}
