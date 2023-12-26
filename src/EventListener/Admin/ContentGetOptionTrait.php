<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Symfony\Component\HttpFoundation\Request;

trait ContentGetOptionTrait
{
    protected function getOption(Request $request, string $optionName): mixed
    {
        $contentConfig = $request->attributes->get('_content_config');

        return $contentConfig['admin'][get_called_class()::ACTION_NAME][$optionName] ?? null;
    }
}
