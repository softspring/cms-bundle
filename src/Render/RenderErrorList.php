<?php

namespace Softspring\CmsBundle\Render;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Throwable;

class RenderErrorList
{
    protected array $errors = [];
    protected array $location = [];

    public function add(string $template, Throwable $exception, array $contextData): void
    {
        $this->errors[] = [
            'location' => $this->currentLocation(),
            'template' => $template,
            'exception' => $exception,
            'contextData' => $contextData,
        ];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsAsString(): array
    {
        return array_map(function ($error) {
            return sprintf('%s (%s): %s', $error['location'], $error['template'], $error['exception']->getMessage());
        }, $this->getErrors());
    }

    public function pushLocation($location): void
    {
        $this->location = array_merge($this->location, is_array($location) ? $location : [$location]);
    }

    public function popLocation(): ?string
    {
        return array_pop($this->location);
    }

    public function resetLocation(): void
    {
        $this->location = [];
    }

    public function currentLocation(): string
    {
        return implode('', array_map(fn ($loc) => "[$loc]", $this->location));
    }

    /**
     * @throws RenderErrorException
     */
    public function buildExceptionOnErrors(): void
    {
        if (!$this->hasErrors()) {
            return;
        }

        throw new RenderErrorException($this);
    }

    public function formMapErrors(FormInterface $form): void
    {
        foreach ($this->getErrors() as $error) {
            $paths = array_map(fn ($v) => trim($v, '[]'), explode('][', $error['location']));
            $field = $this->getMappedForm($form, $paths);
            $field && $field->addError(new FormError('An error has been produced during module render. Please review module configuration and try again. If the problem persists contact with developers.', null, [], null, $error));
        }
    }

    protected function getMappedForm(?FormInterface $form, array $paths): ?FormInterface
    {
        if (empty($paths)) {
            return $form;
        }

        $path = array_shift($paths);

        return $form->has($path) ? $this->getMappedForm($form->get($path), $paths) : null;
    }
}
