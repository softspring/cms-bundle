<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlameListener implements EventSubscriberInterface
{
    public function __construct(protected ?Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_APPLY => [
                ['onCreateVersion', 5],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_APPLY => [
                ['onPublishVersion', 5],
            ],
        ];
    }

    public function onCreateVersion(ApplyEvent $event): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $user = $this->security->getUser();
        $version = $event->getEntity();
        $userData = [
            'id' => $user->getUserIdentifier(),
        ];

        if (method_exists($user, 'getDisplayName')) {
            $userData['name'] = $user->getDisplayName();
        }

        $version->setMetaField('creator', $userData);
    }

    public function onPublishVersion(ApplyEvent $event): void
    {
        if (!$this->security->getUser()) {
            return;
        }

        $user = $this->security->getUser();

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        $published = $version->getMetaField('published', []);
        $userData = [
            'id' => $user->getUserIdentifier(),
        ];
        if (method_exists($user, 'getDisplayName')) {
            $userData['name'] = $user->getDisplayName();
        }
        $published[] = [
            'action' => 'publish',
            'date' => new \DateTime(),
            'user' => $userData,
        ];
        $version->setMetaField('published', $published);

        if ($content->getPublishedVersion()) {
            $published = $content->getPublishedVersion()->getMetaField('published', []);

            $userData = [
                'id' => $user->getUserIdentifier(),
            ];

            if (method_exists($user, 'getDisplayName')) {
                $userData['name'] = $user->getDisplayName();
            }

            $published[] = [
                'action' => 'unpublish',
                'date' => new \DateTime(),
                'user' => $userData,
                'new_version' => 'v'.$version->getVersionNumber(),
            ];

            $content->getPublishedVersion()->setMetaField('published', $published);
        }
    }
}
