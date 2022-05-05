<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Response;

use ExeGeseIT\EzGEDWsClient\Core\EzGEDResponseAbstract;

/**
 * Description of UploadResponse
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class UploadResponse extends EzGEDResponseAbstract
{
    private ?string $filePath = null;
    
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }
    
    protected function initialize(array $data): void
    {
        $this->error = isset($data['success']) && $data['success'] ? 0 : 1; 
        $this->filePath = isset($data['filePath']) && $data['filePath'];
        unset($data['filePath']);
        unset($data['success']);
    }

}
