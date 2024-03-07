<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\Render\RenderErrorException;

trait ExceptionMessageTrait
{
    protected function extractExceptionMessage(\Throwable $exception): string
    {
        return '<ul>'.implode('', $this->extractExceptionMessageEntries($exception)).'</ul>';
    }

    private function extractExceptionMessageEntries(\Throwable $exception): array
    {
        $messages[] = sprintf('<li><strong>%s</strong>: %s</li>', get_class($exception), $exception->getMessage());

        if ($exception->getPrevious()) {
            $messages = array_merge($messages, $this->extractExceptionMessageEntries($exception->getPrevious()));
        }

        if ($exception instanceof RenderErrorException) {
            $messages = array_merge($messages, [
                '<ul>'.implode('', array_map(function ($error) {
                    return sprintf('<li>%s</li>', $error);
                }, $exception->getRenderErrorList()->getErrorsAsString())).'</ul>',
            ]);
        }

        return $messages;
    }
}
