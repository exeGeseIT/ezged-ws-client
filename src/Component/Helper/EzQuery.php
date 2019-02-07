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

namespace JcgDev\EzGEDWsClient\Component\Helper;

/**
 * Description of EzQuery
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzQuery extends EzBagGeneric
{

    public function __construct() {
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
    public function init( $stdClass ) {
        if ( $this->validateData($stdClass,['QRY_ID','QRY_DESC']) ) {

            foreach ($stdClass as $property => $value) {
                if ( $property !== 'rows') {
                    $this->setProperty($property,$value);
                }
            }
        }
        return $this;
    }

}
