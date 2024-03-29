<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
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
        protected EntityManagerInterface $em,
        protected CmsConfig $cmsConfig,
        protected ContentVersionCompiler $contentCompiler,
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

        $compiled = $contentVersion->getCompiled();

        if (!isset($compiled[$compiledKey])) {
            $compiledModules = $contentVersion->getCompiledModules()[$compiledKey] ?? null;
            $contentVersion->setCompiledModules($compiledModules);
            $compiled[$compiledKey] = $this->contentCompiler->compileRequest($contentVersion, $request, $compiledModules);
            $contentVersion->setCompiled($compiled);
            $this->saveEntity($contentVersion);
        }

        return $compiled[$compiledKey];
    }
}
