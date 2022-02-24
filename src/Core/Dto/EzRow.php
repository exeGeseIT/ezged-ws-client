<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

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
     * @param iterable $data
     * @return self
     */
    public function init(iterable $data): self
    {
        foreach ($data as $property => $value) {
            if ('rows' !== $property) {
                $this->setProperty($property, $value);
            }

            //Autodetermination de la PK
            $matches = [];
            if (null === $this->getPkField() && preg_match('/(^.*)_ID$/i',$property,$matches)) {
                $this->pkField = $matches[0];
                $this->tablename = $matches[1];
            }
        }
        return $this;
    }
}
