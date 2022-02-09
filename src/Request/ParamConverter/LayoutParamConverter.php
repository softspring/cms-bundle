<?php

namespace Softspring\CmsBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Softspring\CmsBundle\Model\LayoutInterface;
use Softspring\CrudlBundle\Manager\CrudlEntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LayoutParamConverter implements ParamConverterInterface
{
    protected CrudlEntityManagerInterface $manager;

    public function __construct(CrudlEntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $query = $request->attributes->get($configuration->getName());
        $entity = $this->manager->getRepository()->findOneBy(['id' => $query]);
        $request->attributes->set($configuration->getName(), $entity);
    }

    public function supports(ParamConverter $configuration)
    {
        return LayoutInterface::class === $configuration->getClass();
    }
}