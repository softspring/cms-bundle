<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UnpublishListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'unpublish';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_LOAD_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_NOT_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_FOUND => [],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_APPLY => [
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_SUCCESS => [
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_FAILURE => [
                ['onFailure', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_UNPUBLISH_EXCEPTION => [],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        $entity = $event->getEntity();
        $entity->setPublishedVersion(null);
        $this->sectionManager->saveEntity($entity);
        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.unpublish.has_been_unpublished_flash', [], 'sfs_cms_admin');

        /** @var SectionInterface $entity */
        $entity = $event->getEntity();
        $event->setResponse($this->redirectBack($entity, $event->getRequest()));
    }

    public function onFailure(FailureEvent $event): void
    {
        $this->flashNotifier->addTrans('error', 'admin_sections.unpublish.failure_flash', ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_admin');

        $url = $this->router->generate('sfs_cms_admin_sections_details', ['section' => $event->getEntity()->getId()]);

        $event->setResponse(new RedirectResponse($url));
    }
}
