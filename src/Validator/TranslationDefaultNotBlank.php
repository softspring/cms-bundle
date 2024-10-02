<?php

namespace Softspring\CmsBundle\Validator;

use Symfony\Component\Validator\Constraint;

class TranslationDefaultNotBlank extends Constraint
{
    public string $message = 'This value should not be blank.';
}
