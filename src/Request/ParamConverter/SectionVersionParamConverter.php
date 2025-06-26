<?php

namespace Softspring\CmsBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since CmsBundle 5.2, will be removed in CmsBundle 6.0
 */
class SectionVersionParamConverter implements ParamConverterInterface
{
    protected SectionVersionManagerInterface $manager;

    public function __construct(SectionVersionManagerInterface $manager)
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
        return SectionVersionInterface::class === $configuration->getClass();
    }
}
