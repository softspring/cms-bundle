<?php

namespace Softspring\CmsBundle\Render\Isolated;

use Softspring\CmsBundle\Render\Exception\IsolatedEnvironmentException;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\LocaleSwitcher;

/**
 * IsolatedAppVariable is a wrapper around AppVariable that prevents access to user, token, and session methods.
 * This is useful in CMS renders where such access is not allowed.
 */
class IsolatedAppVariable extends AppVariable
{
    public function __construct(protected AppVariable $inner)
    {
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function getUser(): ?UserInterface
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'getUser');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function getToken(): ?TokenInterface
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'getToken');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function getSession(): ?SessionInterface
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'getSession');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function getFlashes(array|string|null $types = null): array
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'getFlashes');
    }

    public function getRequest(): ?Request
    {
        return $this->inner->getRequest();
    }

    public function getEnvironment(): string
    {
        return $this->inner->getEnvironment();
    }

    public function getDebug(): bool
    {
        return $this->inner->getDebug();
    }

    public function getLocale(): string
    {
        return $this->inner->getLocale();
    }

    public function getEnabled_locales(): array
    {
        return $this->inner->getEnabled_locales();
    }

    public function getCurrent_route(): ?string
    {
        return $this->inner->getCurrent_route();
    }

    public function getCurrent_route_parameters(): array
    {
        return $this->inner->getCurrent_route_parameters();
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setTokenStorage');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setRequestStack');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setEnvironment(string $environment): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setEnvironment');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setDebug(bool $debug): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setDebug');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setLocaleSwitcher(LocaleSwitcher $localeSwitcher): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setLocaleSwitcher');
    }

    /**
     * @throws IsolatedEnvironmentException
     */
    public function setEnabledLocales(array $enabledLocales): void
    {
        throw new IsolatedEnvironmentException(IsolatedAppVariable::class, 'setEnabledLocales');
    }
}
