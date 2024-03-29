<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 * Description of EzRow
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzRecord extends EzGenericBag
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
        $_pks = [];
        foreach ($data as $property => $value) {
            if ('rows' !== $property) {
                $this->setProperty($property, $value);
            }

            $matches = [];
            if (preg_match('/(^.*)_ID$/i',$property,$matches)) {
                $_pks[] = $matches;
            }
        }
        
        //Autodetermination de la PK
        $matches = [];
        if (null === $this->getPkField() && 1 === count($_pks)) {
            $this->pkField = $_pks[0][0];
            $this->tablename = $_pks[0][1];
        }
        
        return $this;
    }
}
