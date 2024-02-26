<?php

namespace Softspring\CmsBundle\EventListener\Admin\Media;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\SfsMediaEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MediaReadListener implements EventSubscriberInterface
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsMediaEvents::ADMIN_MEDIAS_READ_VIEW => 'onMediaReadViewAddDependantVersions',
        ];
    }

    public function onMediaReadViewAddDependantVersions(ViewEvent $event): void
    {
        /** @var MediaInterface $media */
        $media = $event->getData()['media'];

        $versions = $this->em->createQueryBuilder()
            ->from(ContentVersionInterface::class, 'v')
            ->select('v')
            ->leftJoin('v.content', 'c')
            ->where(':media MEMBER OF v.medias')
            ->setParameter('media', $media)
            ->getQuery()
            ->getResult();

        $event->getData()['linked_cms_versions'] = $versions;
    }
}
