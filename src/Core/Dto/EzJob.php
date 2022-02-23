<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 * Description of EzJob
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzJob extends EzGenericBag
{

    /**
     *  {
     *      "type": "addpages",
     *      "JOBQUEUE_ID": 2439,
     *  }
     */
    public function __construct()
    {
        parent::__construct();
        $this->pkField = 'JOBQUEUE_ID';
    }

}
