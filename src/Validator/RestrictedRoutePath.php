<?php

namespace Softspring\CmsBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class RestrictedRoutePath extends Constraint
{
    public string $restrictedPathMessage = 'The path "{{ path }}" is restricted.';
    public ?array $fields = null;

    public function __construct(
        ?array $options = null,
        ?array $fields = null,
        ?string $restrictedPathMessage = null,
    ) {
        $this->fields = $fields ?? $options['fields'] ?? null;
        $this->restrictedPathMessage = $restrictedPathMessage ?? $options['restricted_path_message'] ?? $this->restrictedPathMessage;
        parent::__construct();
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
