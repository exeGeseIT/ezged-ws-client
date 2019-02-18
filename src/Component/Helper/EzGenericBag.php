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
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGenericBag extends EzBagAbstract
{
    protected $pkField = null;
    protected $table = null;


    /**
     *
     * @param string|null $pkField
     * @param string|null $table
     */
    public function __construct( $pkField = null, $table = null) {
        $this->pkField = $pkField;
        $this->table = $table;
        parent::__construct();
    }


    public function getPKField() {
        return $this->pkField;
    }

    public function getTable() {
        return $this->table;
    }

    /**
     *
     * @return int|null
     */
    public function getId() {
        $pkfield = strtolower($this->getPKField());
        if ( empty($pkfield) ) {
            return null;
        }
        return (array_key_exists($pkfield,$this->_properties) ? $this->_properties[$pkfield] : null);
    }

    /**
     * @param object $stdClass
     */
    public function init( $stdClass ) {
        if ( $this->validateData($stdClass) ) {
            foreach ($stdClass as $property => $value) {
                if ( $property === 'rows') {
                    foreach ($value as $element) {
                        $this->elements[] = (new EzGenericBag())->init($element);
                    }
                } else {
                    $this->setProperty($property,$value);
                }
            }
        }
        return $this;
    }

}
