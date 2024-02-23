<?php

namespace Softspring\CmsBundle\Manager;

use Softspring\CmsBundle\Model\ContentInterface;

interface ContentEntityDuplicatorInterface
{
    public function supports(ContentInterface $content): bool;

    public function duplicateData(ContentInterface $oldContent, ContentInterface $newContent): void;
}
