<?php

namespace Softspring\CmsBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since CmsBundle 5.2, will be removed in CmsBundle 6.0
 */
class ContentParamConverter implements ParamConverterInterface
{
    protected ContentManagerInterface $manager;

    public function __construct(ContentManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $query = $request->attributes->get($configuration->getName());
        $entity = $this->manager->getRepository()->findOneBy(['id' => $query]);

        if (!$entity) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $entity);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return ContentInterface::class === $configuration->getClass();
    }
}
