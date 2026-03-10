<?php

namespace Softspring\CmsBundle\Admin\ActionListener;

use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Throwable;

trait ExceptionMessageTrait
{
    protected function extractExceptionMessage(Throwable $exception): string
    {
        return '<ul>'.implode('', $this->extractExceptionMessageEntries($exception)).'</ul>';
    }

    private function extractExceptionMessageEntries(Throwable $exception): array
    {
        $messages[] = sprintf('<li><strong>%s</strong>: %s</li>', get_class($exception), $exception->getMessage());

        if ($exception->getPrevious() instanceof Throwable) {
            $messages = array_merge($messages, $this->extractExceptionMessageEntries($exception->getPrevious()));
        }

        if ($exception instanceof RenderErrorException) {
            $messages = array_merge($messages, [
                '<ul>'.implode('', array_map(function (string $error): string {
                    return sprintf('<li>%s</li>', $error);
                }, $exception->getRenderErrorList()->getErrorsAsString())).'</ul>',
            ]);
        }

        return $messages;
    }
}
