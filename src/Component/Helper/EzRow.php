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
 * Description of EzRow
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzRow extends EzGenericBag
{

    /**
     *
     *  {
     *      "NOTEDEFRAIS_NOM_FOU": "",
     *      "NOTEDEFRAIS_ID": 11,
     *      "NOTEDEFRAIS_MONTANT_TTC": 0,
     *      "FACTURE_SCOPE_LBL": null,
     *      "NOTEDEFRAIS_FT": "",
     *      "NOTEDEFRAIS_SALARIE": "",
     *      "NOTEDEFRAIS_VALID": 0,
     *      "NOTEDEFRAIS_DATE": null,
     *      "NOTEDEFRAIS_REJECTED": 0,
     *      "type": "row"
     *  }
     *
     * @param object $stdClass
     */
    public function init($stdClass)
    {
        $this->data = $stdClass;
        foreach ($stdClass as $property => $value) {
            if ($property !== 'rows') {
                $this->setProperty($property,$value);
            }

            //Autodetermination de la PK
            $matches = [];
            if (null === $this->getPKField() && preg_match('/(^.*)_ID$/i',$property,$matches)) {
                $this->pkField = $matches[0];
                $this->table = $matches[1];
            }
        }
        return $this;
    }

}
