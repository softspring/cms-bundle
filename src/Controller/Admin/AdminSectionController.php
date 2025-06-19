<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Softspring\CmsBundle\Compiler\CompileAllException;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Form\Admin\Section\SectionCreateFormInterface;
use Softspring\CmsBundle\Form\Admin\Section\SectionListFilterFormInterface;
use Softspring\CmsBundle\Form\Admin\Section\SectionUpdateFormInterface;
use Softspring\CmsBundle\Form\Admin\SectionVersion\VersionCreateFormInterface;
use Softspring\CmsBundle\Form\Admin\SectionVersion\VersionListFilterForm;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Utils\SitesSorter;
use Softspring\Component\DoctrinePaginator\Exception\InvalidFormTypeException;
use Softspring\Component\DoctrinePaginator\Paginator;
use Softspring\Component\DoctrineQueryFilters\Exception\InvalidFilterValueException;
use Softspring\Component\DoctrineQueryFilters\Exception\MissingFromInQueryBuilderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class AdminSectionController extends AbstractController
{
    public function __construct(
        protected SectionManagerInterface $sectionManager,
        protected SectionVersionManagerInterface $sectionVersionManager,
        protected EntityManagerInterface $em,
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
    ) {
    }

    /**
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     * @throws InvalidFormTypeException
     */
    public function list(Request $request, SectionListFilterFormInterface $formType): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_LIST')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(get_class($formType))->handleRequest($request);
        [$qb, $page, $rpp, $filters, $orderSort, $filtersMode] = Paginator::processPaginatedFilterForm($form, $request);
        $sections = Paginator::queryPage($qb, $page, $rpp, $filters, $orderSort, $filtersMode);

        return $this->render('@SfsCms/admin/section/list.html.twig', [
            'filter_form' => $form->createView(),
            'sections' => $sections,

            // @deprecated
            'filterForm' => $form,
            'entities' => $sections,
        ]);
    }

    public function create(Request $request, SectionCreateFormInterface $form): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_CREATE')) {
            throw $this->createAccessDeniedException();
        }

        $section = $this->sectionManager->createEntity();
        $form = $this->createForm(get_class($form), $section)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sectionManager->saveEntity($section);
            $this->addFlash('success', 'admin_sections.create_success_flash');

            return $this->redirectToRoute('sfs_cms_admin_sections_list');
        }

        return $this->render('@SfsCms/admin/section/create.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
        ]);
    }

    public function update(SectionInterface $section, Request $request, SectionUpdateFormInterface $form): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_UPDATE', $section)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(get_class($form), $section)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sectionManager->saveEntity($section);
            $this->addFlash('success', 'admin_sections.update_success_flash');

            return $this->redirectToRoute('sfs_cms_admin_sections_details', ['section' => $section->getId()]);
        }

        return $this->render('@SfsCms/admin/section/update.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
        ]);
    }

    public function read(SectionInterface $section): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_READ', $section)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@SfsCms/admin/section/read.html.twig', [
            'section' => $section,
            'lastestVersions' => $this->sectionVersionManager->getLatestVersions($section, 3),
        ]);
    }

    public function createVersion(SectionInterface $section, Request $request, VersionCreateFormInterface $formType, ?SectionVersionInterface $prevVersion = null): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_CREATE', $section)) {
            throw $this->createAccessDeniedException();
        }

        $prevVersion = $prevVersion ?: $section->getLastVersion(); // todo move to createVersion in manager
        $version = $this->sectionManager->createVersion($section, $prevVersion, VersionInterface::ORIGIN_EDIT);
        $prevVersion && $version->setOriginDescription('v'.$prevVersion->getVersionNumber()); // todo move to createVersion in manager

        $form = $this->createForm(get_class($formType), $version, [
            'section' => $section,
        ])->handleRequest($request);

        try {
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $this->sectionVersionManager->saveEntity($version);
                    $this->addFlash('success', 'admin_sections.create_version_success_flash');

                    return $this->redirectBack($section, $request, $version);
                }

                $alert = ['warning', 'admin_sections.content.validation_error'];
            }
        } catch (Exception $exception) {
            if ($exception instanceof RenderErrorException) {
                $exception->getRenderErrorList()->formMapErrors($form);
                $alert = ['error', 'admin_sections.content.render_error', ['%exception%' => $exception->getMessage()]];
            } elseif ($exception->getPrevious() instanceof RenderErrorException) {
                $exception->getPrevious()->getRenderErrorList()->formMapErrors($form);
                $alert = ['error', 'admin_sections.content.render_error', ['%exception%' => $exception->getMessage()]];
            } elseif ($exception instanceof CompileException) {
                $alert = ['error', 'admin_sections.content.render_error', ['%exception%' => $exception->getMessage()]];
            } elseif ($exception instanceof CompileAllException) {
                $alert = ['error', 'admin_sections.content.render_error', ['%exception%' => $exception->getMessage()]];
            } else {
                $alert = ['error', 'admin_sections.content.render_error', ['%exception%' => $exception->getMessage()]];
            }
        }

        return $this->render('@SfsCms/admin/section/version_create.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'version' => $version,
            'maxInputVars' => ini_get('max_input_vars'),
            'prev_version' => $prevVersion,
            'alert' => $alert ?? null,

            // @deprecated
            'section_entity' => $section,
        ]);
    }

    public function preview(SectionInterface $section, Request $request): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_READ', $section)) {
            throw $this->createAccessDeniedException();
        }

        $version = $section->getLastVersion();

        if (!$version) {
            throw $this->createNotFoundException('No version found for this section');
        }

        return $this->render('@SfsCms/admin/section/preview.html.twig', [
            'section' => $section,
            'version' => $version,
            'availableLocales' => $this->cmsHelper->locale()->getEnabledLocales(),
            'defaultLocale' => $this->cmsHelper->locale()->getDefaultLocale(),
            'sites' => SitesSorter::sort($this->cmsHelper->config()->getSites()),
        ]);
    }

    public function previewVersion(SectionInterface $section, SectionVersionInterface $version, Request $request,
        SiteManagerInterface $siteManager,
        SectionVersionCompiler $sectionVersionCompiler,
        ?WebDebugToolbarListener $webDebugToolbarListener = null,
    ): Response {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_READ', $section)) {
            throw $this->createAccessDeniedException();
        }

        $webDebugToolbarListener && $webDebugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);

        $request->setLocale($request->query->get('_locale', $request->getLocale()));

        $site = $request->query->has('_site') ? $siteManager->getRepository()->findOneById($request->query->get('_site')) : $this->cmsHelper->config()->getSites()[0] ?? null;
        $request->attributes->set('_site', "$site");
        $request->attributes->set('_sfs_cms_site', $site);

        $request->attributes->set('_cms_preview', true);

        $compiledData = $sectionVersionCompiler->compileRequest($version, $request, false);

        if (!$compiledData->hasErrors()) {
            return $this->render('@SfsCms/admin/section/preview_layout.html.twig', [
                'content' => $compiledData->getDataPart('content'),
            ]);
        } else {
            return new Response($compiledData->getDataPart('content') ?? '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function versionsList(SectionInterface $section, Request $request): Response
    {
        if (!$this->isGranted('PERMISSION_SFS_CMS_ADMIN_SECTIONS_VERSIONS')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(VersionListFilterForm::class, null, ['section' => $section])->handleRequest($request);
        [$qb, $page, $rpp, $filters, $orderSort, $filtersMode] = Paginator::processPaginatedFilterForm($form, $request);
        $versions = Paginator::queryPage($qb, $page, $rpp, $filters, $orderSort, $filtersMode);

        return $this->render('@SfsCms/admin/section/version_list.html.twig', [
            'filter_form' => $form->createView(),
            'section' => $section,
            'versions' => $versions,
        ]);
    }

    protected function redirectBack(SectionInterface $section, Request $request, ?SectionVersionInterface $version = null): RedirectResponse
    {
        switch ($request->request->get('goto', $request->query->get('back'))) {
            case 'versions':
                $page = $request->query->get('page', 1);

                return new RedirectResponse($this->router->generate(name: 'sfs_cms_admin_sections_versions', parameters: ['section' => $section, 'page' => $page]));

            case 'version_info':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_version_info', ['section' => $section, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate(name: 'sfs_cms_admin_sections_versions', parameters: ['section' => $section]));

            case 'content':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_content_from_version', ['section' => $section, 'prevVersion' => $version]));
                }

                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_content', ['section' => $section]));

            case 'preview':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_preview', ['section' => $section, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_preview', ['section' => $section]));

            case 'publish':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_publish_version', ['section' => $section, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_publish_version', ['section' => $section]));

            case 'details':
            default:
                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_details', ['section' => $section]));
        }
    }
}
