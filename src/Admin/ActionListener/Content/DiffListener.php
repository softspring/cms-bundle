<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Content;

use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DiffListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'diff';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected string $contentCacheType,
        protected FormFactoryInterface $formFactory,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onViewAddConfig', 0],
                ['onViewSetTemplate', 0],
                ['onViewAddEntities', 0],
                ['onViewCreateFormAndProcessIt', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DIFF_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onViewCreateFormAndProcessIt(ViewEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $content = $event->getData()['content'];

        $form = $this->formFactory->create(
            $contentConfig['admin']['diff']['form'],
            null,
            [
                'content' => $content,
                'content_config' => $contentConfig,
            ]
        );

        $form->handleRequest($event->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $version1 = $form->get('version1')->getData();
            $version2 = $form->get('version2')->getData();
            $event->getData()['version1'] = $version1;
            $event->getData()['version2'] = $version2;
        }

        $event->getData()['form'] = $form->createView();
    }
}
