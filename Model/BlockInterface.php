<?php

namespace Softspring\CmsBundle\Model;

interface BlockInterface
{
    /**
     * @return string|null
     */
    public function getKey(): ?string;

    /**
     * @param string|null $key
     */
    public function setKey(?string $key): void;

    /**
     * @return array|null
     */
    public function getContent(): ?array;

    /**
     * @param array|null $content
     */
    public function setContent(?array $content): void;
}