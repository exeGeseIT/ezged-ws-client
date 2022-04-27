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
        parent::__construct($pkField = 'QRY_ID');
        
        $this->setProperties([
            'type',
            'QRY_DESC',
            'QRY_ID',
            'QRY_ASK',
            'QRY_RANK',
            'QRY_PUBCNTCOR',
            'QRY_TREE',
        ]);
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
     * @param iterable $data
     * @return self
     */
    public function init(iterable $data): self
    {
        if ( $this->validateData($data,['QRY_ID','QRY_DESC']) ) {
            
            foreach ($data as $property => $value) {
                if ('rows' === $property) {
                    $this->setProperty($property, $value);
                }
            }
        }
        return $this;
    }

}
