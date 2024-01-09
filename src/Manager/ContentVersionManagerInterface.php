<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContentVersionManagerInterface extends CrudlEntityManagerInterface
{
    /**
     * @return ContentVersionInterface
     */
    public function createEntity(): object;

    /**
     * @psalm-param ContentVersionInterface $entity
     */
    public function saveEntity(object $entity): void;

    /**
     * @psalm-param ContentVersionInterface $entity
     */
    public function deleteEntity(object $entity): void;

    public function getLatestVersions(ContentInterface $content, int $limit = 3): Collection;

    public function getCompiledContent(ContentVersionInterface $contentVersion, Request $request): string;
}
