<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzGenericBag extends EzBagAbstract
{
    protected ?string $pkField = null;
    protected ?string $tablename = null;


    /**
     *
     * @param string|null $pkField
     * @param string|null $table
     */
    public function __construct(?string $pkField = null, ?string $table = null)
    {
        parent::__construct();
        $this->pkField = $pkField;
        $this->tablename = $table;
    }


    public function getPkField(): ?string
    {
        return $this->pkField;
    }

    public function getTable(): ?string
    {
        return $this->tablename;
    }

    /**
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $pkfield = strtolower($this->getPkField() ?? '');
        return $this->propertiesBag->has($pkfield) ? $this->getProperty($pkfield) : null;
    }

    /**
     * @param iterable $rawData
     * @return self
     */
    public function init(iterable $rawData): self
    {
        if ( $this->validateData($rawData) ) {
            foreach ($rawData as $property => $value) {
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
