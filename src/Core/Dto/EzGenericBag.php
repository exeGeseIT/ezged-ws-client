<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGenericBag extends EzBagAbstract
{
    protected string $pkField = null;
    protected string $table = null;


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


    public function getPkField(): string
    {
        return $this->pkField;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $pkfield = strtolower($this->getPkField());
        if ( empty($pkfield) ) {
            return null;
        }
        return array_key_exists($pkfield, $this->_properties) ? $this->_properties[ $pkfield ] : null;
    }

    /**
     * @param iterable $data
     */
    public function init(iterable $data): self
    {
        if ( $this->validateData($data) ) {
            foreach ($data as $property => $value) {
                if ('rows' === $property) {
                    foreach ($value as $element) {
                        $this->elements[] = (new EzGenericBag())->init($element);
                    }
                } else {
                    $this->setProperty($property, $value);
                }
            }
        }
        return $this;
    }

}
