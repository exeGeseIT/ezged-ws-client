<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 * Description of EzQuery
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzQuery extends EzGenericBag
{

    public function __construct()
    {
        parent::__construct();

        $this->pkField = 'QRY_ID';
        
        $properties = ['type', 'QRY_DESC', 'QRY_ID','QRY_ASK','QRY_RANK','QRY_PUBCNTCOR','QRY_TREE'];
        $this->setProperties($properties);
    }


    /**
     *
     *  {
     *      "type":"query",
     *      "QRY_ASK": 0,
     *      "type": "query",
     *      "QRY_ID": 53,
     *      "QRY_DESC": "Recherche tous mes courriers",
     *      "SRCHQRYFLT_INCLUDED": 1,
     *      "QRY_RANK": 5,
     *      "QRY_PUBCNTCOR": 0,
     *      "QRY_TREE": 0
     *  }
     *
     * @param object $stdClass
     */
    public function init($stdClass)
    {
        if ($this->validateData($stdClass,['QRY_ID','QRY_DESC'])) {

            foreach ($stdClass as $property => $value) {
                if ( $property !== 'rows') {
                    $this->setProperty($property,$value);
                }
            }
        }
        return $this;
    }

}
