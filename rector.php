<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Symfony61\Rector\Class_\CommandConfigureToAttributeRector;
use Rector\Symfony\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector;
use Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths(array_values(array_filter([
        is_dir(__DIR__.'/src') ? __DIR__.'/src' : null,
        is_dir(__DIR__.'/tests') ? __DIR__.'/tests' : null,
    ])))
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
    ])
    ->withImportNames()
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withComposerBased(symfony: true)
    ->withSkip([
        CommandConfigureToAttributeRector::class,
        PushRequestToRequestStackConstructorRector::class,
        WrapReturnRector::class,
        TypedPropertyFromCreateMockAssignRector::class,
    ]);
