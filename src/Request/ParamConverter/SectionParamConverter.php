<?php

namespace Softspring\CmsBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since CmsBundle 5.2, will be removed in CmsBundle 6.0
 */
class SectionParamConverter implements ParamConverterInterface
{
    protected SectionManagerInterface $manager;

    public function __construct(SectionManagerInterface $manager)
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
        return SectionInterface::class === $configuration->getClass();
    }
}
