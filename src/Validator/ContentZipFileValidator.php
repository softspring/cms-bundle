<?php

namespace Softspring\CmsBundle\Validator;

use Softspring\CmsBundle\Utils\ZipContent;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentZipFileValidator extends FileValidator
{
    /**
     * @param UploadedFile|string|null $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentZipFile) {
            throw new UnexpectedTypeException($constraint, ContentZipFile::class);
        }

        $violations = \count($this->context->getViolations());

        parent::validate($value, $constraint);

        $failed = \count($this->context->getViolations()) !== $violations;

        if ($failed || null === $value || '' === $value) {
            return;
        }

        $originalName = $value->getClientOriginalName();
        $fields = [];
        if (!preg_match('/(.*)-(v\d+)-(\d{4}-\d{2}-\d{2})-(\d{2}-\d{2}-\d{2}).zip$/', $originalName, $fields)) {
            $this->context->buildViolation($constraint->invalidNameFormat)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }

        if (false === ($zip = ZipContent::read($value->getPath(), $value->getBasename()))) {
            $this->context->buildViolation($constraint->canNotOpenFile)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }

        if (empty($zip['contents'])) {
            $this->context->buildViolation($constraint->doesNotContainContentFile)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
