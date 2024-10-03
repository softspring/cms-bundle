<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\CompileException;
use Softspring\CmsBundle\Render\ContentVersionCompiler;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;
use Symfony\Component\HttpFoundation\Request;

class ContentVersionManager implements ContentVersionManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface       $em,
        protected CmsConfig                    $cmsConfig,
        protected ContentVersionCompiler       $contentCompiler,
        protected CompiledDataManagerInterface $compiledDataManager,
    )
    {
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
        $newContentVersion->setOrigin(ContentVersionInterface::ORIGIN_DUPLICATE);
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
     */
    public function getCompiledContent(ContentVersionInterface $contentVersion, Request $request): string
    {
        $compiledKey = $this->contentCompiler->getCompileKeyFromRequest($contentVersion, $request);

        /** @var ?CompiledDataInterface $compiledData */
        $compiledData = $this->compiledDataManager->getRepository()->findOneBy([
            'contentVersion' => $contentVersion,
            'key' => $compiledKey,
        ]);

        if (!$compiledData || !$compiledData->getDataPart('content')) {
            if (!$compiledData) {
                $compiledData = $this->compiledDataManager->createEntity();
                $compiledData->setKey($compiledKey);
                $compiledData->setContentVersion($contentVersion);
                $contentVersion->addCompiled($compiledData);
            }

            $compiledContent = $this->contentCompiler->compileRequest($contentVersion, $request, $compiledData->getDataPart('modules'));
            $compiledData->setDataPart('content', $compiledContent);
            $this->saveEntity($contentVersion);
        }

        return $compiledData->getDataPart('content');
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
        foreach ($module as $fieldName => &$field) {
            if (in_array($fieldName, ['_module', '_revision'])) {
                continue;
            } else if ($fieldName === 'modules' && is_array($field)) {
                foreach ($field as &$subModule) {
                    $this->addLocaleToModule($subModule, $locale);
                }
            } elseif (isset($field['_trans_id'])) {
                $field[$locale] = null;
            } elseif ('locale_filter' === $fieldName) {
                if (!empty($field)) {
                    // if locale filter is not empty, add the locale to the filter
                    $field[] = $locale;
                }
            }
        }
    }
}
