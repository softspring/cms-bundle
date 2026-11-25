<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Block;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validation;

class BlockValidationMappingTest extends TestCase
{
    public function testBlockNameMustBeUnique(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->addYamlMapping(__DIR__.'/../../../config/validation/BlockInterface.yaml')
            ->getValidator();

        $metadata = $validator->getMetadataFor(Block::class);
        $this->assertInstanceOf(ClassMetadata::class, $metadata);

        $uniqueConstraints = array_values(array_filter(
            $metadata->getConstraints(),
            static fn (Constraint $constraint): bool => $constraint instanceof UniqueEntity,
        ));

        $this->assertCount(1, $uniqueConstraints);
        $this->assertSame(['name'], (array) $uniqueConstraints[0]->fields);
    }
}
