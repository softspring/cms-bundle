<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;

class LockListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_lock';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_INITIALIZE => [
                ['onEventLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_LOAD_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_NOT_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_APPLY => [
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_SUCCESS => [
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_FAILURE => [
                ['onFailure', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_EXCEPTION => [],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var SectionVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('lock') ?: false);

        $this->sectionVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        /** @var SectionVersionInterface $version */
        $version = $event->getEntity();

        if ($version->isKeep()) {
            $this->flashNotifier->addTrans('success', 'admin_sections.version_lock.success_locked_flash', [], 'sfs_cms_admin');
        } else {
            $this->flashNotifier->addTrans('success', 'admin_sections.version_lock.success_unlocked_flash', [], 'sfs_cms_admin');
        }

        $section = $event->getRequest()->attributes->get('section');

        $event->setResponse($this->redirectBack($section, $event->getRequest(), $version));
    }

    public function onFailure(FailureEvent $event): void
    {
        $this->flashNotifier->addTrans('error', 'admin_sections.version_lock.failed_flash', ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_admin');

        $section = $event->getRequest()->attributes->get('section');

        $event->setResponse($this->redirectBack($section, $event->getRequest()));
    }
}
