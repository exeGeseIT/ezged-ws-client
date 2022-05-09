<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 * Description of EzFile
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzFile extends EzGenericBag
{
    protected ?int $rsid;
    protected ?int $fsfileid;
    protected ?string $fsfileripe;
    
    protected string $gedlink;
    protected string $previewUrl;
    protected string $thumbnailUrl;
    
    public function __construct(string $apiUrl)
    {
        parent::__construct($pkField = 'fsfileid');
        
        $this->setProperties([
            'rsid', 
            'fsfileid', 
            'ripefilearchive',
        ]);
        
        $this->apiUrl = $apiUrl;
        
    }
    
    public function getRsid(): int
    {
        return $this->getProperty('rsid');
    }
    
    public function getFsfileid(): int
    {
        return $this->getProperty('fsfileid');
    }

    public function getFsfileripe(): string
    {
        return $this->getProperty('ripefilearchive');
    }
    
    /**
     * Return the request query part needed to visualise le file
     *   ie: <$ezgedUrl>/data/showdocs.php?<$gedlink>
     * @return string
     */
    public function getGedlink(): string
    {
        return $this->gedlink;
    }
    
    public function getPreviewUrl(): string
    {
        return $this->previewUrl;
    }
    
    public function getDirectlink(): string
    {
        return str_replace(
            ['data/showdocs', 'fsfileid=', 'fsfileripe='],
            ['directlink', 'id1=', 'id2='], 
            $this->previewUrl
        );
    }


    /**
     * {
     *    "hasnot": 0,
     *    "ripe": "184c24b9db6fa2d212e3e6538e5f02553b36d1d0",
     *    "datefilearchive": "2022-02-23 18:22:29",
     *    "DOCPAK_RANK": 1,
     *    "lock": "",
     *    "oname": "ingenieur-en-informatique-62166d30e4da9482240016.jpeg",
     *    "rank": 1,
     *    "fssigid": 0,
     *    "ostamp": "2022-02-23 18:21:52",
     *    "DOCPAK_ID": 255802,
     *    "sigusers": [],
     *    "isused": "0",
     *    "table": "documentrecrutement",
     *    "infwords": "",
     *    "size": 1805189,
     *    "namefilearchive": "4P8C5DB1",
     *    "haswords": 0,
     *    "dimensions": 0,
     *    "DOCPAK_FSFILEID": 256144,
     *    "QRY_DESC": "",
     *    "sizefileorigin": 1805189,
     *    "fsfileid": 256144,
     *    "rsid": 249387,
     *    "version": 0,
     *    "DOCPAK_RSID": 249387,
     *    "hassig": "0",
     *    "DOCPAK_REVISION": 0,
     *    "type": "docpak",
     *    "userfilearchive": "admin",
     *    "used": "0",
     *    "islock": "0",
     *    "ripefileorigin": "184c24b9db6fa2d212e3e6538e5f02553b36d1d0",
     *    "docpakid": 255802,
     *    "namefileorigin": "ingenieur-en-informatique-62166d30e4da9482240016.jpeg",
     *    "datefileorigin": "2022-02-23 18:21:52",
     *    "mime": "image/jpeg",
     *    "user": "admin",
     *    "path": "d:\\nchp\\var\\nchp\\instance\\EMDOM\\DEFTSA\\00000001\\18\\4c\\4P8C5DB1.jpeg",
     *    "usedby": "0",
     *    "pages": 0,
     *    "sigpath": "",
     *    "extension": "jpeg",
     *    "ripefilearchive": "184c24b9db6fa2d212e3e6538e5f02553b36d1d0",
     *    "ext": "jpeg",
     *    "DOCPAK_TBL": "documentrecrutement",
     *    "pathfilearchive": "d:\\nchp\\var\\nchp\\instance\\EMDOM\\DEFTSA\\00000001\\18\\4c\\4P8C5DB1.jpeg",
     *    "nname": "4P8C5DB1",
     *    "newripe": "184c24b9db6fa2d212e3e6538e5f02553b36d1d0",
     *    "issuers": [],
     *    "nstamp": "2022-02-23 18:22:29",
     *    "sigripe": "",
     *    "fssigripe": ""
     *  },
     *
     * @param iterable $rawData
     * @return self
     */
    public function init(iterable $rawData): self
    {
        if ( $this->validateData($rawData, ['rank','rsid','fsfileid','ripefilearchive']) ) {
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
        
        $this->gedlink = sprintf('fsfileid=%d&fsfileripe=%s', $this->getFsfileid(), $this->getFsfileripe());
        $this->previewUrl = sprintf('%s/showdocs.php?%s', $this->apiUrl, $this->getGedlink());
        $this->thumbnailUrl = sprintf('%s/showthumbs.php?%s', $this->apiUrl, $this->getGedlink());
        
        return $this;
    }

}
