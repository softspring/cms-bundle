<?php

namespace Softspring\CmsBundle\Request\ValueResolver;

use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use function is_object;

class SectionValueResolver implements ValueResolverInterface
{
    public function __construct(protected SectionManagerInterface $manager)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (is_object($request->attributes->get($argument->getName()))) {
            return [];
        }

        if (SectionInterface::class !== $argument->getType()) {
            return [];
        }

        $query = $request->attributes->get($argument->getName());
        $entity = $this->manager->getRepository()->findOneBy(['id' => $query]);

        if (!$entity) {
            return [];
        }

        return [$entity];
    }
}
