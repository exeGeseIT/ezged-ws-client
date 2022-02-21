<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 * Description of EzFamily
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzFamily extends EzGenericBag
{

    public function __construct()
    {
        parent::__construct();
        
        $this->pkField = 'QRYSET_ID';
        
        $properties = ['type', 'QRYSET_DESC', 'QRYSET_ID'];
        $this->setProperties($properties);
    }


    /**
     *
     * {
        "type":"family",
        "QRYSET_DESC":"Courriers",
        "QRYSET_ID":"1",
        "rows":[
           {
              "type":"query",
              "QRY_ASK":"0",
              "QRY_ID":"2",
              "QRY_DESC":"Courriers à corriger",
              "QRY_RANK":"20",
              "QRY_PUBCNTCOR":"1",
              "QRY_TREE":"0"
           }
        ]
     }
     *
     * @param object $stdClass
     */
    public function init($stdClass)
    {
        if ($this->validateData($stdClass,['QRYSET_ID','QRYSET_DESC','rows'])) {

            $this->setProperty('type', 'family')
                 ->setProperty('QRYSET_ID', $stdClass->QRYSET_ID)
                 ->setProperty('QRYSET_DESC', $stdClass->QRYSET_DESC);

            foreach ($stdClass->rows as $queryObj) {
                $this->elements[] = (new EzQuery())->init($queryObj);
            }
        }
        return $this;
    }

}
