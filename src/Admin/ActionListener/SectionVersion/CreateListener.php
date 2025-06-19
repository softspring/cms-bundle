<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormInvalidEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\Component\PolymorphicFormType\Form\Exception\MissingFormTypeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Section action.
 */
class CreateListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_create';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_INITIALIZE => [
                ['onEventLoadSectionEntity', 9],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_ENTITY => [
                ['onCreateEntity', 1],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_FORM_PREPARE => [
                ['onFormPrepareResolve', 0],
            ],
            //            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_FORM_INIT => [
            //            ],
            //            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_FORM_VALID => [
            //            ],
            //            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_APPLY => [
            //            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_SUCCESS => [
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_FAILURE => [
                ['onFailureShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_FORM_INVALID => [
                ['onFormInvalidShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_VIEW => [
                ['onView', 0],
            ],
            //            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CREATE_EXCEPTION => [
            //            ],
        ];
    }

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();

        /** @var SectionInterface $section */
        $section = $request->attributes->get('section');
        $prevVersion = $request->attributes->get('prevVersion');

        if ($prevVersion) {
            $prevVersion = $section->getVersions()->filter(fn (SectionVersionInterface $version) => $version->getId() == $prevVersion)->first();
        }

        $request->attributes->set('prevVersion', $prevVersion ?: $section->getLastVersion());
        $version = $this->sectionManager->createVersion($section, $prevVersion, SectionVersionInterface::ORIGIN_EDIT);
        $prevVersion && $version->setOriginDescription('v'.$prevVersion->getVersionNumber());

        $request->attributes->set('version', $version);

        $event->setEntity($version);
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'section' => $event->getRequest()->attributes->get('section'),
            'method' => 'POST',
        ]);
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $sectionConfig = $request->attributes->get('_section_config');
        $version = $event->getEntity();
        $section = $version->getSection();

        $this->flashNotifier->addTrans('success', 'admin_sections.section.success_saved', [], 'sfs_cms_sections');

        switch ($request->request->get('goto')) {
            case 'content':
                $url = $this->router->generate('sfs_cms_admin_sections_content', ['section' => $section]);
                $event->setResponse(new RedirectResponse($url));
                break;

            case 'preview':
                $url = $this->router->generate('sfs_cms_admin_sections_preview', ['section' => $section]);
                $event->setResponse(new RedirectResponse($url));
                break;

            case 'publish':
                $url = $this->router->generate('sfs_cms_admin_sections_publish_version', ['section' => $section, 'version' => $version]);
                $event->setResponse(new RedirectResponse($url));
                break;

            default:
                if ($redirectTo = $this->getOption($request, 'success_redirect_to')) {
                    $event->setResponse(new RedirectResponse($this->router->generate($redirectTo, ['section' => $section])));
                } else {
                    $event->setResponse($this->redirectBack('', $section, $request));
                }
        }
    }

    public function onFailureShowAlert(FailureEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getException();
        $sectionConfig = $request->attributes->get('_section_config');

        if ($exception instanceof RenderErrorException) {
            $exception->getRenderErrorList()->formMapErrors($event->getForm());
            $request->attributes->set('_section_version_alert', ['error', 'admin_sections.section.render_error', ['%exception%' => $exception->getMessage()]]);
        } elseif ($exception->getPrevious() instanceof RenderErrorException) {
            $exception->getPrevious()->getRenderErrorList()->formMapErrors($event->getForm());
            $request->attributes->set('_section_version_alert', ['error', 'admin_sections.section.render_error', ['%exception%' => $exception->getMessage()]]);
        } elseif ($exception instanceof CompileException) {
            $request->attributes->set('_section_version_alert', ['error', 'admin_sections.section.render_error', ['%exception%' => $exception->getMessage()]]);
        } else {
            $request->attributes->set('_section_version_alert', ['error', 'admin_sections.section.render_error', ['%exception%' => $exception->getMessage()]]);
        }
    }

    public function onFormInvalidShowAlert(FormInvalidEvent $event): void
    {
        $request = $event->getRequest();

        if (1 == $event->getForm()->getErrors()->count() && '_ok' == $event->getForm()->getErrors()[0]->getOrigin()->getName()) {
            return;
        }

        $sectionConfig = $request->attributes->get('_section_config');

        $request->attributes->set('_section_version_alert', ['warning', 'admin_sections.section.validation_error']);
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        $request = $event->getRequest();
        /** @var SectionInterface $section */
        $section = $request->attributes->get('section');
        /** @var SectionVersionInterface $version */
        $version = $request->attributes->get('version');

        // preview mode
        $request->attributes->set('_cms_preview', true);

        // show alert if exists
        $event->getData()['alert'] = $request->attributes->get('_section_version_alert');

        //        // add enabled locales
        //        $sitesLocales = $section->getSites()->map(fn (SiteInterface $site) => $site->getConfig()['locales'])->toArray();
        //        $enabledLocales = call_user_func_array('array_merge', $sitesLocales);
        //        $enabledLocales = array_unique($enabledLocales);

        // add max_input_vars to prevent errors
        // @see https://www.php.net/manual/en/info.configuration.php#ini.max-input-vars
        $event->getData()['maxInputVars'] = ini_get('max_input_vars');

        // add prev version
        $event->getData()['prev_version'] = $request->attributes->get('prevVersion');
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onException(ExceptionEvent $event): void
    {
        $sectionConfig = $event->getRequest()->attributes->get('_section_config');

        if ($event->getException() instanceof MissingFormTypeException) {
            $this->flashNotifier->addTrans('error', 'admin_sections.version_create.module_not_configured_flash', ['%module%' => $event->getException()->getDiscriminator()], 'sfs_cms_sections');

            $url = $this->router->generate("sfs_cms_admin_section_{$event->getRequest()->attributes->get('_section_config')['_id']}_details", ['section' => $event->getRequest()->attributes->get('section')]);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
