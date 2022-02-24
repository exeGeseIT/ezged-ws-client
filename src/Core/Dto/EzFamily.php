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
        parent::__construct($pkField = 'QRYSET_ID');
        
        $this->setProperties([
            'type', 
            'QRYSET_DESC', 
            'QRYSET_ID',
        ]);
    }


    /**
     *
     * {
     *   "type":"family",
     *   "QRYSET_DESC":"Courriers",
     *   "QRYSET_ID":"1",
     *   "rows":[
     *      {
     *         "type":"query",
     *         "QRY_ASK":"0",
     *         "QRY_ID":"2",
     *         "QRY_DESC":"Courriers à corriger",
     *         "QRY_RANK":"20",
     *         "QRY_PUBCNTCOR":"1",
     *         "QRY_TREE":"0"
     *      }
     *   ]
     * }
     *
     * @param iterable $rawData
     * @return self
     */
    public function init(iterable $rawData): self
    {
        if ( $this->validateData($rawData,['type','QRYSET_ID','QRYSET_DESC','rows']) ) {
            
            $this
                ->setProperty('type', self::extract('type',$rawData))
                ->setProperty('QRYSET_ID', self::extract('QRYSET_ID',$rawData))
                ->setProperty('QRYSET_DESC', self::extract('QRYSET_DESC',$rawData))
                ;

            foreach (self::extract('rows',$rawData) as $queryObj) {
                $this->elements[] = (new EzQuery())->init($queryObj);
            }
        }
        return $this;
    }

}
