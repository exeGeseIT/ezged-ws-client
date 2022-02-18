<?php

/*
 * The MIT License
 *
 * Copyright 2019 Jean-Claude GLOMBARD <jc.glombard@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ExeGeseIT\EzGEDWsClient\Component\Helper;

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
