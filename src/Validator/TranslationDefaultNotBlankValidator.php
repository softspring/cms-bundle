<?php

namespace Softspring\CmsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TranslationDefaultNotBlankValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TranslationDefaultNotBlank) {
            throw new UnexpectedTypeException($constraint, TranslationDefaultNotBlank::class);
        }

        $defaultLocale = $value['_default'] ?? null;

        if (null === $defaultLocale) {
            return;
        }

        if (empty($value[$defaultLocale])) {
            // children[data].children[main].children[0].children[title].data.children[
            $this->context->buildViolation($constraint->message)
                ->atPath("[$defaultLocale]")
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(NotBlank::IS_BLANK_ERROR)
                ->addViolation()
            ;
        }
    }
}
