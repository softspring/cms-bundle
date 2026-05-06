<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\ActionListener;

use Symfony\Component\HttpFoundation\Request;

trait ContentGetOptionTrait
{
    protected function getOption(Request $request, string $optionName): mixed
    {
        $contentConfig = $request->attributes->get('_content_config');

        return $contentConfig['admin'][get_called_class()::ACTION_NAME][$optionName] ?? null;
    }
}
