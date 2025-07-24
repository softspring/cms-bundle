<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;
use Symfony\Component\HttpFoundation\Request;

class ContentVersionManager implements ContentVersionManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected CmsHelper $cmsHelper,
        protected ContentVersionCompiler $contentCompiler,
        protected CompiledDataManagerInterface $compiledDataManager,
        protected bool $contentSaveCompiled,
    ) {
    }

    public function getTargetClass(): string
    {
        return ContentVersionInterface::class;
    }

    public function duplicateEntity(ContentVersionInterface $contentVersion, ?ContentInterface $content = null, ?string $originDescription = null): ContentVersionInterface
    {
        /** @var ContentVersionInterface $newContentVersion */
        $newContentVersion = $this->createEntity();
        $newContentVersion->setContent($content ?? $contentVersion->getContent());
        $newContentVersion->setData($contentVersion->getData());
        $newContentVersion->setSeo($contentVersion->getSeo());
        $newContentVersion->setOrigin(VersionInterface::ORIGIN_DUPLICATE);
        $newContentVersion->setOriginDescription($originDescription);
        $newContentVersion->setLayout($contentVersion->getLayout());

        return $newContentVersion;
    }

    public function getLatestVersions(ContentInterface $content, int $limit = 3): Collection
    {
        return new ArrayCollection($this->getRepository()->createQueryBuilder('cv')
            ->where('cv.content = :content')
            ->setParameter('content', $content)
            ->orderBy('cv.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult());
    }

    /**
     * @throws CompileException
     * @throws Exception
     */
    public function getCompiledContent(ContentVersionInterface $contentVersion, Request $request, bool $throwExceptionOnCompileError = true): CompiledDataInterface
    {
        $compiledKey = $this->compiledDataManager->getCompileKeyFromRequest($contentVersion, $request);

        /** @var ?CompiledDataInterface $compiledData */
        $compiledData = $this->compiledDataManager->getRepository()->findOneBy([
            'contentVersion' => $contentVersion,
            'key' => $compiledKey,
        ]);

        if (!$compiledData?->getDataPart('content') || !$this->cmsHelper->compile()->contentSaveCompiled($contentVersion)) {
            $compiledData = $this->contentCompiler->compileRequest($contentVersion, $request, $compiledData);

            if ($throwExceptionOnCompileError && $compiledData->hasErrors()) {
                throw new CompileException('Compilation errors occurred: '.implode(', ', $compiledData->getDataPart('errors')));
            }

            $this->cmsHelper->compile()->contentSaveCompiled($contentVersion) && $this->saveEntity($contentVersion);
        }

        return $compiledData;
    }

    public function addLocale(ContentVersionInterface $contentVersion, string $locale): void
    {
        $data = $contentVersion->getData();
        foreach ($data as &$container) {
            foreach ($container as &$module) {
                $this->addLocaleToModule($module, $locale);
            }
        }
        $contentVersion->setData($data);
    }

    protected function addLocaleToModule(array &$module, string $locale): void
    {
        foreach ($module as $fieldName => &$fieldValue) {
            if (in_array($fieldName, ['_module', '_revision'])) {
                continue;
            } elseif ('modules' === $fieldName && is_array($fieldValue)) {
                foreach ($fieldValue as &$subModule) {
                    $this->addLocaleToModule($subModule, $locale);
                }
            } elseif (is_array($fieldValue) && isset($fieldValue['_trans_id'])) {
                $fieldValue[$locale] = null;
            } elseif ('locale_filter' === $fieldName) {
                if (!empty($fieldValue)) {
                    // if locale filter is not empty, add the locale to the filter
                    $fieldValue[] = $locale;
                }
            }
        }
    }

    public function addSite(ContentVersionInterface $contentVersion, SiteInterface $site): void
    {
        $data = $contentVersion->getData();
        foreach ($data as &$container) {
            foreach ($container as &$module) {
                $this->addSiteToModule($module, $site);
            }
        }
        $contentVersion->setData($data);
    }

    protected function addSiteToModule(array &$module, SiteInterface $site): void
    {
        foreach ($module as $fieldName => &$fieldValue) {
            if (in_array($fieldName, ['_module', '_revision'])) {
                continue;
            } elseif ('modules' === $fieldName && is_array($fieldValue)) {
                foreach ($fieldValue as &$subModule) {
                    $this->addSiteToModule($subModule, $site);
                }
            } elseif ('site_filter' === $fieldName) {
                if (!empty($fieldValue)) {
                    // if site filter is not empty, add the site to the filter
                    $fieldValue[] = $site;
                }
            }
        }
    }
}
