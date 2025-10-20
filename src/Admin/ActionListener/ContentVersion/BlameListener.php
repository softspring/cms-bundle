<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use DateTime;
use Softspring\CmsBundle\Model\VersionableInterface;
use Softspring\CmsBundle\Model\VersionInterface;
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
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_LOCK_APPLY => [
                ['onLockVersion', 5],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_APPLY => [
                ['onRecompileVersion', 5],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_APPLY => [
                ['onCreateVersion', 6],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_APPLY => [
                ['onDuplicateVersion', 6],
            ],
        ];
    }

    public function onCreateVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var VersionInterface $version */
        $version = $event->getEntity();
        $version->setMetaField('creator', $this->getUser());
        $this->addHistory($version, 'create');
    }

    public function onDuplicateVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var VersionableInterface $versionable */
        $versionable = $event->getEntity();
        $version = $versionable->getLastVersion();

        $version->setMetaField('creator', $this->getUser());
        $this->addHistory($version, 'duplicate');
    }

    public function onLockVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var VersionInterface $version */
        $version = $event->getEntity();

        // this runs before the version is updated, so we check the value inverted
        !$version->isKeep() && $this->addHistory($version, 'lock');
        $version->isKeep() && $this->addHistory($version, 'unlock');
    }

    public function onRecompileVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var VersionInterface $version */
        $version = $event->getEntity();
        $this->addHistory($version, 'recompile');
    }

    public function onPublishVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var VersionInterface $version */
        $version = $event->getEntity();
        /** @var VersionableInterface $versionable */
        $versionable = $event->getRequest()->attributes->get('content');

        $this->addHistory($version, 'publish');

        if ($versionable->getPublishedVersion()) {
            $this->addHistory($versionable->getPublishedVersion(), 'unpublish', [
                'new_version' => 'v'.$version->getVersionNumber(),
            ]);
        }
    }

    protected function canBlame(): bool
    {
        return $this->security && $this->security->getUser();
    }

    protected function getUser(): array
    {
        $user = $this->security->getUser();

        $userData = [
            'id' => $user->getUserIdentifier(),
        ];

        if (method_exists($user, 'getDisplayName')) {
            $userData['name'] = $user->getDisplayName();
        }

        return $userData;
    }

    protected function addHistory(VersionInterface $version, string $action, array $extra = []): void
    {
        $history = $version->getMetaField('history', []);

        $date = new DateTime();

        $history[] = [
            'action' => $action,
            'date' => [
                'date' => $date->format('Y-m-d H:i:s'),
                'timezone' => $date->getTimezone()->getName(),
            ],
            'user' => $this->getUser(),
            'extra' => $extra,
        ];
        $version->setMetaField('history', $history);
    }
}
