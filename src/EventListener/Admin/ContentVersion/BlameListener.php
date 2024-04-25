<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use DateTime;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security as SecurityOld;

class BlameListener implements EventSubscriberInterface
{
    /** @phpstan-ignore-next-line  */
    protected SecurityOld|Security|null $security;

    /** @phpstan-ignore-next-line  */
    public function __construct(?SecurityOld $securityOld, ?Security $security)
    {
        $this->security = $securityOld ?? $security;
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

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $version->setMetaField('creator', $this->getUser());
        $this->addHistory($version, 'create');
    }

    public function onDuplicateVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var ContentInterface $content */
        $content = $event->getEntity();
        $version = $content->getVersions()->first();
        $version->setMetaField('creator', $this->getUser());
        $this->addHistory($version, 'duplicate');
    }

    public function onLockVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var ContentVersionInterface $version */
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

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $this->addHistory($version, 'recompile');
    }

    public function onPublishVersion(ApplyEvent $event): void
    {
        if (!$this->canBlame()) {
            return;
        }

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        $this->addHistory($version, 'publish');

        if ($content->getPublishedVersion()) {
            $this->addHistory($content->getPublishedVersion(), 'unpublish', [
                'new_version' => 'v'.$version->getVersionNumber(),
            ]);
        }
    }

    protected function canBlame(): bool
    {
        /* @phpstan-ignore-next-line */
        return $this->security && $this->security->getUser();
    }

    protected function getUser(): array
    {
        /** @phpstan-ignore-next-line  */
        $user = $this->security->getUser();

        $userData = [
            'id' => $user->getUserIdentifier(),
        ];

        if (method_exists($user, 'getDisplayName')) {
            $userData['name'] = $user->getDisplayName();
        }

        return $userData;
    }

    protected function addHistory(ContentVersionInterface $contentVersion, string $action, array $extra = []): void
    {
        $history = $contentVersion->getMetaField('history', []);

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
        $contentVersion->setMetaField('history', $history);
    }
}
