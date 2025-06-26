<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;

class UnpublishListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'unpublish';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_FOUND => [],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_APPLY => [
                ['onApplyUnpublish', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_FAILURE => [
                ['onFailureAddFlash', 10],
                ['onFailureRedirectBack', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_EXCEPTION => [],
        ];
    }

    public function onApplyUnpublish(ApplyEvent $event): void
    {
        $entity = $event->getEntity();
        $entity->setPublishedVersion(null);
        $this->sectionManager->saveEntity($entity);
        $event->setApplied(true);
    }

    public function onSuccessAddFlash(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.unpublish.has_been_unpublished_flash', [], 'sfs_cms_admin');
    }
}
