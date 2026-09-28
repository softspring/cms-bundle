<?php

namespace Softspring\CmsBundle\Tests;

use Softspring\CmsBundle\Form\Type\ClassType;
use Softspring\CmsBundle\Form\Type\ColorType;
use Softspring\CmsBundle\Form\Type\CssType;
use Softspring\CmsBundle\Form\Type\HtmlType;
use Softspring\CmsBundle\Form\Type\IdType;
use Softspring\CmsBundle\Form\Type\LinkType;
use Softspring\CmsBundle\Form\Type\MediaType;
use Softspring\CmsBundle\Form\Type\MediaVersionType;
use Softspring\CmsBundle\Form\Type\SymfonyRouteType;
use Softspring\CmsBundle\Form\Type\TranslatableType;
use Softspring\CmsBundle\Form\Type\TranslationType;
use Softspring\Component\DynamicFormType\Form\Resolver\DefaultTypeResolver;
use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolverInterface;

class CmsModuleTypeResolver implements TypeResolverInterface
{
    private const CMS_TYPES = [
        'class' => ClassType::class,
        'color' => ColorType::class,
        'css' => CssType::class,
        'html' => HtmlType::class,
        'id' => IdType::class,
        'link' => LinkType::class,
        'media' => MediaType::class,
        'mediaVersion' => MediaVersionType::class,
        'symfonyRoute' => SymfonyRouteType::class,
        'translatable' => TranslatableType::class,
        'translation' => TranslationType::class,
    ];

    private DefaultTypeResolver $defaultTypeResolver;

    public function __construct()
    {
        $this->defaultTypeResolver = new DefaultTypeResolver();
    }

    public function resolveTypeClass(?string $type): ?string
    {
        if (isset(self::CMS_TYPES[$type])) {
            return self::CMS_TYPES[$type];
        }

        return $this->defaultTypeResolver->resolveTypeClass($type);
    }

    public function getPossibleFormClasses(string $type): array
    {
        return $this->defaultTypeResolver->getPossibleFormClasses($type);
    }
}
