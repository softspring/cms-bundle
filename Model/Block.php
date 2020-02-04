<?php

namespace Softspring\CmsBundle\Model;

abstract class Block implements BlockInterface
{
    /**
     * @var string|null
     */
    protected $key;

    /**
     * @var array|null
     */
    protected $content;

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string|null $key
     */
    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return array|null
     */
    public function getContent(): ?array
    {
        return $this->content;
    }

    /**
     * @param array|null $content
     */
    public function setContent(?array $content): void
    {
        $this->content = $content;
    }
}