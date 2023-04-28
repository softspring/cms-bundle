<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

trait PropagateLabelFormatTrait
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $childName => $childView) {
            if (isset($view->children[$childName]->vars['label_format'])) {
                //                $view->children[$childName]->vars['label_format'] = str_replace('.form.', ".form.{$view->vars['name']}.", $view->children[$childName]->vars['label_format']);
                $view->children[$childName]->vars['label_format'] = str_replace('.%name%.', ".{$view->vars['name']}.%name%.", $view->children[$childName]->vars['label_format']);
            }
        }
    }
}
