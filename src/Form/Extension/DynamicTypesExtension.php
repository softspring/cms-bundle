<?php

namespace Softspring\CmsBundle\Form\Extension;

use Softspring\CmsBundle\Form\Module\ContainerModuleType;
use Softspring\CmsBundle\Form\Module\DynamicFormModuleType;
use Softspring\CmsBundle\Form\Type\DynamicFormType;
use Softspring\Component\DynamicFormType\Form\Extension\Type\DynamicTypesExtension as BaseDynamicTypesExtension;

class DynamicTypesExtension extends BaseDynamicTypesExtension
{
    public static function getExtendedTypes(): iterable
    {
        return array_merge((array) parent::getExtendedTypes(), [
            DynamicFormModuleType::class,
            DynamicFormType::class,
            ContainerModuleType::class,
        ]);
    }
}
