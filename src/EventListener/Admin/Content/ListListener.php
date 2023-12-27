<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;

class ListListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'list';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_LIST_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_LIST_FILTER_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFilterFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_LIST_FILTER_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_LIST_FILTER => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_LIST_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_LIST_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFilterFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($contentConfig['admin']['list']['filter_form']);
        $event->setFormOptions([
            'method' => 'GET',
            'content_config' => $contentConfig,
            'class' => $this->contentManager->getTypeClass($contentConfig['_id']),
        ]);
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $event->getData()['list_page_view'] = $contentConfig['admin']['list']['page_view'];
        // 'filterForm' => $form->createView(),
        // 'read_route' => $config['read_route'] ?? null,

        if ($event->getRequest()->isXmlHttpRequest()) {
            $event->setTemplate($contentConfig['admin']['list']['page_view']);
        }
    }
}
