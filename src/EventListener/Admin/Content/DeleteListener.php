<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\FormValidEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'delete';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onValidModifyModel', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DELETE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($contentConfig['admin']['delete']['type']);
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $contentConfig,
            'entity' => $event->getEntity(),
        ]);
    }

    public function onValidModifyModel(FormValidEvent $event): void
    {
        /** @var ContentInterface $entity */
        $entity = $event->getForm()->getData();
        $form = $event->getForm();

        switch ($form->get('action')->getData()) {
            case 'change':
                foreach ($entity->getRoutes() as $route) {
                    $route->setContent($form->get('content')->getData());
                }
                break;

            case 'redirect':
                foreach ($entity->getRoutes() as $route) {
                    $route->setContent(null);
                    $route->setType(RouteInterface::TYPE_REDIRECT_TO_ROUTE);
                    $route->setRedirectType(Response::HTTP_MOVED_PERMANENTLY); // 301
                    $route->setSymfonyRoute($form->get('symfonyRoute')->getData());
                }
                break;

            case 'delete':
            default:
                // do nothing, route will be deleted with cascade
        }
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.delete.success_flash", [], 'sfs_cms_contents');

        if (empty($contentConfig['admin']['delete']['success_redirect_to'])) {
            $contentConfig['admin']['delete']['success_redirect_to'] = "sfs_cms_admin_content_{$contentConfig['_id']}_list";
        }

        $redirectUrl = $this->router->generate($contentConfig['admin']['delete']['success_redirect_to']);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);
        $event->getData()['entity'] = $event->getData()['content'];
    }
}
