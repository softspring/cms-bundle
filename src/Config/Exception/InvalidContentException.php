<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Config\Exception;

use Exception;

class InvalidContentException extends Exception
{
    public function __construct(protected string $contentType, array $contentsConfig)
    {
        parent::__construct(sprintf('Invalid content type "%s", posible values: %s', $contentType, implode(', ', array_keys($contentsConfig))));
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
