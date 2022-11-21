<?php

namespace Softspring\CmsBundle\Form;

use Softspring\Component\DynamicFormType\Form\DynamicFormTrait as BaseDynamicFormTrait;

trait DynamicFormTrait
{
    use BaseDynamicFormTrait;

    protected function getFormClasses(string $type): array
    {
        return [
            'App\Form\Type\\'.ucfirst($type).'Type',
            'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
            'Symfony\Component\Form\Extension\Core\Type\\'.ucfirst($type).'Type',
            'Symfony\Bridge\Doctrine\Form\Type\\'.ucfirst($type).'Type',
        ];
    }
}
