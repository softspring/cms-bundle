<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use DateTime;
use Softspring\CmsBundle\Model\VersionableInterface;
use Softspring\CmsBundle\Model\VersionInterface;
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
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_APPLY => [
                ['onCreateVersion', 5],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_PUBLISH_APPLY => [
                ['onPublishVersion', 5],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_LOCK_APPLY => [
                ['onLockVersion', 5],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_APPLY => [
                ['onRecompileVersion', 5],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_DUPLICATE_APPLY => [
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
        $version = $versionable->getVersions()->first();
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
        $versionable = $event->getRequest()->attributes->get('section');

        $this->addHistory($version, 'publish');

        if ($versionable->getPublishedVersion()) {
            $this->addHistory($versionable->getPublishedVersion(), 'unpublish', [
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
