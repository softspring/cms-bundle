<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Utils\SitesSorter;
use Softspring\Component\CrudlController\Event\ViewEvent;

class PreviewListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'preview';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_LOAD_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_NOT_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_FOUND => [],
            SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_VIEW => [
                ['onView', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_PREVIEW_EXCEPTION => [],
        ];
    }

    public function onView(ViewEvent $event): void
    {
        /** @var SectionInterface $section */
        $section = $event->getRequest()->attributes->get('section');

        if ($event->getRequest()->query->get('version')) {
            $version = $this->sectionVersionManager->getRepository()->findOneBy([
                'section' => $section,
                'id' => $event->getRequest()->query->get('version'),
            ]);
        }

        $version = $version ?? $section->getLastVersion();

        $event->getData()['version'] = $version;
        $event->getData()['availableLocales'] = $this->cmsHelper->locale()->getEnabledLocales();
        $event->getData()['defaultLocale'] = $this->cmsHelper->locale()->getDefaultLocale();
        $event->getData()['sites'] = SitesSorter::sort($this->cmsHelper->config()->getSites());
    }
}
