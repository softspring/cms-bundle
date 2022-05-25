<?php

namespace Softspring\CmsBundle\Model;

use Softspring\CmsBundle\Entity\Embeddable\Seo;

interface ContentVersionInterface
{
    public function getId(): ?string;

    public function getContent(): ?ContentInterface;

    public function setContent(?ContentInterface $content): void;

    public function getLayout(): ?string;

    public function setLayout(?string $layout): void;

    public function getData(): ?array;

    public function setData(?array $data): void;
}
