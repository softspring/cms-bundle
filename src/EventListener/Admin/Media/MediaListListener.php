<?php

namespace Softspring\CmsBundle\EventListener\Admin\Media;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\SfsMediaEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MediaListListener implements EventSubscriberInterface
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsMediaEvents::ADMIN_MEDIAS_LIST_VIEW => 'onMediaListViewAddDependantVersions',
        ];
    }

    public function onMediaListViewAddDependantVersions(ViewEvent $event): void
    {
        $event->getData()['linked_cms_versions'] = [];

        $mediaIds = array_map(function (MediaInterface $media) {
            return "'{$media->getId()}'";
        }, $event->getData()['entities']->toArray());

        if (!empty($mediaIds)) {
            $sql = 'SELECT media_id, COUNT(DISTINCT c.id) contents, COUNT(DISTINCT cv.id) versions, IF (COUNT(DISTINCT c.id)=1, GROUP_CONCAT(c.name), NULL) as universion_content_name
FROM cms_content_version_medias cvm 
LEFT JOIN cms_content_version cv ON cvm.content_version_id=cv.id
LEFT JOIN cms_content c ON cv.content_id=c.id
WHERE media_id IN (' . implode(',', $mediaIds) . ')
GROUP BY media_id';

            $results = $this->em->getConnection()->executeQuery($sql)->fetchAllAssociative();

            foreach ($results as $result) {
                $event->getData()['linked_cms_versions'][$result['media_id']] = $result;
            }
        }
    }
}
