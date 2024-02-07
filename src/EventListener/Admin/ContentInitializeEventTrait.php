<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\MissingContentTypeException;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait ContentInitializeEventTrait
{
    protected FlashNotifier $flashNotifier;
    protected CmsConfig $cmsConfig;

    public function onInitializeGetConfig(InitializeEvent $event): void
    {
        $request = $event->getRequest();

        try {
            // get configuration, and throw exception if not found
            $contentConfig = $this->getContentConfig($request);

            // store configuration in request attributes
            $request->attributes->set('_content_config', $contentConfig);
        } catch (MissingContentTypeException) {
            $this->flashNotifier->addTrans('warning', 'admin_contents.missing_content_type_flash', [], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse('/'));
        } catch (InvalidContentException $e) {
            $this->flashNotifier->addTrans('warning', 'admin_contents.invalid_content_type_flash', ['%$contentType%' => $e->getContentType()], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse('/'));
        }
    }

    /**
     * @throws InvalidContentException
     * @throws MissingContentTypeException
     */
    protected function getContentConfig(Request $request): array
    {
        if (!$request->attributes->has('_content_type')) {
            throw new MissingContentTypeException('_content_type is required in route defaults');
        }

        return $this->cmsConfig->getContent($request->attributes->get('_content_type')); // required = true
    }

    public function onInitializeIsGranted(InitializeEvent $event): void
    {
        $config = $event->getRequest()->attributes->get('_content_config');

        if (empty($config['admin'][get_called_class()::ACTION_NAME]['is_granted'])) {
            return;
        }

        $this->checkIsGranted($config['admin'][get_called_class()::ACTION_NAME]['is_granted']);
    }

    /**
     * @throws AccessDeniedException
     */
    protected function checkIsGranted(string $role, mixed $subject = null, string $message = 'Access denied, user is not %s.'): void
    {
        if (!$this->authorizationChecker->isGranted($role, $subject)) {
            $exception = new AccessDeniedException(sprintf($message, $role));
            $exception->setAttributes([$role]);
            $exception->setSubject($subject);

            throw $exception;
        }
    }
}
