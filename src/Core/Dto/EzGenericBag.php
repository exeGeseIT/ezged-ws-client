<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

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
    public function __construct(string $pkField = null, string $table = null)
    {
        $this->pkField = $pkField;
        $this->table = $table;
        parent::__construct();
    }


    public function getPkField()
    {
        return $this->pkField;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * @return int|null
     */
    public function getId()
    {
        $pkfield = strtolower($this->getPkField());
        if (empty($pkfield)) {
            return null;
        }
        return (array_key_exists($pkfield,$this->_properties) ? $this->_properties[$pkfield] : null);
    }

    /**
     * @param object $stdClass
     */
    public function init($stdClass)
    {
        if ($this->validateData($stdClass)) {
            foreach ($stdClass as $property => $value) {
                if ($property === 'rows') {
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
