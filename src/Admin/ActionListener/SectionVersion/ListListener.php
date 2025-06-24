<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;

class ListListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_list';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_INITIALIZE => [
                ['onEventLoadSectionEntity', 9],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_FILTER_FORM_PREPARE => [
                ['onFilterFormPrepareResolve', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_FILTER_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_FILTER => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_VIEW => [
                ['onView', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LIST_EXCEPTION => [],
        ];
    }

    public function onFilterFormPrepareResolve(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'method' => 'GET',
            'section' => $event->getRequest()->attributes->get('section'),
        ]);
    }

    //    public function onView(ViewEvent $event): void
    //    {
    //        parent::onView($event);
    //
    //        $event->getData()['list_page_view'] = $this->getOption($event->getRequest(), 'page_view');
    //        // 'filterForm' => $form->createView(),
    //        // 'read_route' => $config['read_route'] ?? null,
    //
    //        if ($event->getRequest()->isXmlHttpRequest()) {
    //            $event->setTemplate($this->getOption($event->getRequest(), 'page_view'));
    //        }
    //    }
}
