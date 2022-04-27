<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzBagInterface
{
    public function init(iterable $rawData): self;
    public function getId(): ?int;
}
