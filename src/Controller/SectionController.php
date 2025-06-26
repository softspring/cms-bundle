<?php

namespace Softspring\CmsBundle\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SectionController extends AbstractController
{
    public function __construct(
        protected SectionManagerInterface $sectionManager,
        protected SectionVersionManagerInterface $sectionVersionManager,
        protected CmsConfig $cmsConfig,
        protected Environment $twig,
        protected ?LoggerInterface $cmsLogger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request, string $section): Response
    {
        return $this->renderById($section, $request);
    }

    /**
     * @throws Exception
     */
    public function renderById(string $section, Request $request): Response
    {
        try {
            /** @var ?SectionInterface $section */
            $section = $this->sectionManager->getRepository()->findOneById($section);

            if (!$section) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing section %s', $section));

                throw $this->createNotFoundException(sprintf('Section with id "%s" not found.', $section));
            }

            /** @var ?SectionVersionInterface $publishedVersion */
            $publishedVersion = $section->getPublishedVersion();

            if (!$publishedVersion) {
                throw $this->createNotFoundException();
            }

            // if ('last_modified' === $this->contentCacheType) {
            //     $response->setEtag(md5($content->getId().$content->getLastModified()?->getTimestamp().$this->contentVersionCompiler->getCompileKeyFromRequest($publishedVersion, $request)));
            //     $response->setLastModified($content->getLastModified());
            //     // Set response as public. Otherwise it will be private by default.
            //     $response->setPublic();
            //     if ($response->isNotModified($request)) {
            //         return $response;
            //     }
            // }

            $response = new Response();

            $sectionContent = $this->sectionVersionManager->getCompiledContent($publishedVersion, $request);

            // create response
            $response->setContent($sectionContent->getDataPart('content'));

            if ($sectionContent->hasErrors()) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            } elseif (/* 'ttl' === $this->contentCacheType && */ $section->getExtra('ttl')) {
                $response->setPublic();
                $response->setMaxAge((int) $section->getExtra('ttl'));
            }

            return $response;
        } catch (Exception $e) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('An error occurred while rendering section with id "%s": %s', $section->getId(), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
    }
}
