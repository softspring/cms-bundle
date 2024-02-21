<?php

namespace Softspring\CmsBundle\EventListener\Admin;

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

        return $messages;
    }
}
