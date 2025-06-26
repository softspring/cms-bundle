<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;

class CleanupVersionsListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'version_cleanup';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_APPLY => [
                ['onApplyCleanupVersions', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_FAILURE => [],
            // SfsCmsEvents::ADMIN_SECTION_CLEANUP_VERSIONS_EXCEPTION => [],
        ];
    }

    public function onApplyCleanupVersions(ApplyEvent $event): void
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
}
