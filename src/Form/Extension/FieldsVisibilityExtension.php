<?php

namespace Softspring\CmsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FieldsVisibilityExtension extends AbstractTypeExtension
{
    public const DATA_FIELDS = [
        'data-show-fields',
        'data-hide-fields',
        'data-empty-fields',
        'data-show-fields-if-checked',
        'data-hide-fields-if-checked',
        'data-empty-fields-if-checked',
        'data-show-fields-if-unchecked',
        'data-hide-fields-if-unchecked',
        'data-empty-fields-if-unchecked',
    ];

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children as $fieldName => $child) {
            if (isset($view->children[$fieldName]->vars['choices'])) {
                /** @var ChoiceView $choice */
                foreach ($view->children[$fieldName]->vars['choices'] as $c => $choice) {
                    foreach (self::DATA_FIELDS as $data) {
                        if (isset($choice->attr[$data])) {
                            $choice->attr[$data] = $this->transformDataFieldsReferences($choice->attr[$data], $view);
                        }
                        if (isset($view->children[$fieldName]->children[$c]->vars['attr'][$data])) {
                            $view->children[$fieldName]->children[$c]->vars['attr'][$data] = $choice->attr[$data];
                        }
                    }
                }
            }

            foreach (self::DATA_FIELDS as $data) {
                if (isset($child->vars['attr'][$data])) {
                    $child->vars['attr'][$data] = $this->transformDataFieldsReferences($child->vars['attr'][$data], $view);
                }
            }
        }
    }

    protected function transformDataFieldsReferences(string $fields, FormView $view): string
    {
        $transformed = [];

        foreach (explode(',', $fields) as $field) {
            $field = trim($field);
            if (!$field) {
                continue;
            }

            if (isset($view->children[$field])) {
                $transformed[] = $view->children[$field]->vars['id'];
            } else {
                $transformed[] = $view->vars['id'].'_'.$field;
            }
        }

        return implode(',', $transformed);
    }
}
