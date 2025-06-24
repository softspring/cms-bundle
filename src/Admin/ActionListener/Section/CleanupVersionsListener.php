<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;

class CleanupVersionsListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'version_cleanup';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_LOAD_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_NOT_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_APPLY => [
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_SUCCESS => [
                ['onSuccess', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_FAILURE => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_EXCEPTION => [],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        $entity = $event->getEntity();

        foreach ($entity->getVersions() as $version) {
            if ($version->deleteOnCleanup()) {
                $entity->removeVersion($version); // TODO THIS SHOULD REMOVE VERSIONS
                $this->sectionVersionManager->deleteEntity($version);
            }
        }
        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $section = $request->attributes->get('section');

        $this->flashNotifier->addTrans('success', 'admin_sections.version_cleanup.success_flash', [], 'sfs_cms_admin');

        $event->setResponse($this->redirectBack($section, $request));
    }
}
