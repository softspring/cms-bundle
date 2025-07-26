<?php

namespace Softspring\CmsBundle\Utils;

readonly class DeprecatedVariable
{
    public function __construct(
        private object $inner,
        private string $oldName,
        private string $newName,
        private string $message = 'The %s variable is deprecated and will be removed in a future version. Please use the new variable %s instead.',
        private string $removeVersion = '6.0',
    ) {
    }

    public function __get(string $name)
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));

        return $this->inner->{$name};
    }

    public function __call(string $name, array $arguments)
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));

        if (!method_exists($this->inner, $name) && method_exists($this->inner, 'get'.ucfirst($name))) {
            // If the method does not exist, but a getter does, we call the getter instead
            $name = 'get'.ucfirst($name);
        }

        return call_user_func([$this->inner, $name], ...$arguments);
    }

    public function __isset(string $name): bool
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));

        return isset($this->inner->{$name});
    }

    public function __set(string $name, $value): void
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));
        $this->inner->{$name} = $value;
    }

    public function __unset(string $name): void
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));
        unset($this->inner->{$name});
    }

    public function __toString(): string
    {
        trigger_deprecation('softspring/cms-bundle', $this->removeVersion, sprintf($this->message, $this->oldName, $this->newName));

        return (string) $this->inner->__toString();
    }
}
