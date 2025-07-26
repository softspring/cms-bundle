<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;
use Symfony\Component\HttpFoundation\Request;

class SectionVersionManager implements SectionVersionManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected CmsHelper $cmsHelper,
        protected SectionVersionCompiler $sectionCompiler,
        protected CompiledDataManagerInterface $compiledDataManager,
    ) {
    }

    public function getTargetClass(): string
    {
        return SectionVersionInterface::class;
    }

    public function duplicateEntity(SectionVersionInterface $sectionVersion, ?SectionInterface $section = null, ?string $originDescription = null): SectionVersionInterface
    {
        /** @var SectionVersionInterface $newSectionVersion */
        $newSectionVersion = $this->createEntity();
        $newSectionVersion->setSection($section ?? $sectionVersion->getSection());
        $newSectionVersion->setData($sectionVersion->getData());
        $newSectionVersion->setOrigin(SectionVersionInterface::ORIGIN_DUPLICATE);
        $newSectionVersion->setOriginDescription($originDescription);

        return $newSectionVersion;
    }

    public function getLatestVersions(SectionInterface $section, int $limit = 3): Collection
    {
        return new ArrayCollection($this->getRepository()->createQueryBuilder('cv')
            ->where('cv.section = :section')
            ->setParameter('section', $section)
            ->orderBy('cv.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult());
    }

    /**
     * @throws CompileException
     * @throws Exception
     */
    public function getCompiledContent(SectionVersionInterface $sectionVersion, Request $request, bool $throwExceptionOnCompileError = true): CompiledDataInterface
    {
        $compiledKey = $this->compiledDataManager->getCompileKeyFromRequest($sectionVersion, $request);

        /** @var ?CompiledDataInterface $compiledData */
        $compiledData = $this->compiledDataManager->getRepository()->findOneBy([
            'sectionVersion' => $sectionVersion,
            'key' => $compiledKey,
        ]);

        if (!$compiledData?->getDataPart('content') || !$this->cmsHelper->compile()->sectionSaveCompiled($sectionVersion)) {
            $compiledData = $this->sectionCompiler->compileRequest($sectionVersion, $request);

            $this->cmsHelper->compile()->sectionSaveCompiled($sectionVersion) && $this->saveEntity($sectionVersion);

            if ($throwExceptionOnCompileError && $compiledData->hasErrors()) {
                throw new CompileException('Compilation error occurred');
            }
        }

        return $compiledData;
    }

    public function addLocale(SectionVersionInterface $sectionVersion, string $locale): void
    {
        $data = $sectionVersion->getData();
        foreach ($data as &$container) {
            foreach ($container as &$module) {
                $this->addLocaleToModule($module, $locale);
            }
        }
        $sectionVersion->setData($data);
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

    public function addSite(SectionVersionInterface $sectionVersion, SiteInterface $site): void
    {
        $data = $sectionVersion->getData();
        foreach ($data as &$container) {
            foreach ($container as &$module) {
                $this->addSiteToModule($module, $site);
            }
        }
        $sectionVersion->setData($data);
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
