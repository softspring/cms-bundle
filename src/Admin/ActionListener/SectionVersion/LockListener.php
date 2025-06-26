<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;

class LockListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_lock';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_INITIALIZE => [
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_APPLY => [
                ['onApplyMarkKeep', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_FAILURE => [
                ['onFailureAddFlash', 10],
                ['onFailureRedirectBack', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_EXCEPTION => [],
        ];
    }

    public function onApplyMarkKeep(ApplyEvent $event): void
    {
        /** @var SectionVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('lock') ?: false);

        $this->sectionVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }

    public function onSuccessAddFlash(SuccessEvent $event): void
    {
        /** @var SectionVersionInterface $version */
        $version = $event->getEntity();

        if ($version->isKeep()) {
            $this->flashNotifier->addTrans('success', 'admin_sections.version_lock.success_locked_flash', [], 'sfs_cms_admin');
        } else {
            $this->flashNotifier->addTrans('success', 'admin_sections.version_lock.success_unlocked_flash', [], 'sfs_cms_admin');
        }
    }
}
