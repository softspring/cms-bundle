<?php

namespace Softspring\CmsBundle\Form;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;

trait DataVisibilityFieldsTrait
{
    protected function transformDataFieldsFinishView(FormView $view, string $fieldName): void
    {
        if (!isset($view->children[$fieldName])) {
            return;
        }

        /** @var ChoiceView $choice */
        foreach ($view->children[$fieldName]->vars['choices'] as $c => $choice) {
            foreach (['data-show-fields', 'data-hide-fields', 'data-empty-fields'] as $data) {
                if (isset($choice->attr[$data])) {
                    $choice->attr[$data] = $this->transformDataFieldsReferences($choice->attr[$data], $view);
                }
                if (isset($view->children[$fieldName]->children[$c]->vars['attr'][$data])) {
                    $view->children[$fieldName]->children[$c]->vars['attr'][$data] = $choice->attr[$data];
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
