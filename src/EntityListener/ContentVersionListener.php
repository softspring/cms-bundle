<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Softspring\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentVersionListener
{
    use TransformEntityValuesTrait;

    protected ContentRender $contentRender;
    protected RequestStack $requestStack;
    protected array $enabledLocales;

    public function __construct(ContentRender $contentRender, RequestStack $requestStack, array $enabledLocales)
    {
        $this->contentRender = $contentRender;
        $this->enabledLocales = $enabledLocales;
        $this->requestStack = $requestStack;
    }

    public function postLoad(ContentVersionInterface $contentVersion, PostLoadEventArgs $event): void
    {
        $this->untransform($contentVersion, $event);
    }

    public function preUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event): void
    {
        $this->transform($contentVersion, $event);
    }

    public function postUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event): void
    {
        $this->saveCompiled($contentVersion, $event);
    }

    public function prePersist(ContentVersionInterface $contentVersion, PrePersistEventArgs $event): void
    {
        $this->saveCompiled($contentVersion, $event);
        $this->transform($contentVersion, $event);
    }

    protected function saveCompiled(ContentVersionInterface $contentVersion, LifecycleEventArgs $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            // if no request provided, probably is running a command, fixtures, etc.
            return;
        }

        $originalLocale = $request->getLocale();
        $originalSite = $request->attributes->get('_sfs_cms_site');

        $compiled = [];
        $compiledModules = [];
        foreach ($contentVersion->getContent()->getSites() as $site) {
            foreach ($this->enabledLocales as $locale) {
                $request->setLocale($locale);
                $request->attributes->set('_sfs_cms_site', $site);
                // $compiled[$locale] = $this->contentRender->render($contentVersion);
                $compiledModules["$site"][$locale] = $this->contentRender->renderModules($contentVersion);
            }
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);

        $request->setLocale($originalLocale);
        $request->attributes->set('_sfs_cms_site', $originalSite);
    }

    protected function transform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $entities = [];
        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                foreach ($module as $field => $value) if ($field !== 'modules') {
                    $data[$layout][$m][$field] = $this->transformEntityValues($value, $event->getObjectManager(), $entities);
                }

                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->transformEntityValues($value, $event->getObjectManager(), $entities);
                        }
                    }
                }
            }
        }
        $contentVersion->setData($data);

        // add media references
        foreach ($entities as $entity) {
            if ($entity instanceof MediaInterface) {
                $contentVersion->addMedia($entity);
            }
        }
        // add route references
        foreach ($entities as $entity) {
            if ($entity instanceof RouteInterface) {
                $contentVersion->addRoute($entity);
            }
        }
    }

    protected function untransform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                foreach ($module as $field => $value) if ($field !== 'modules') {
                    $data[$layout][$m][$field] = $this->untransformEntityValues($value, $event->getObjectManager());
                }

                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->untransformEntityValues($value, $event->getObjectManager());
                        }
                    }
                }
            }
        }
        $contentVersion->setData($data);
    }
}
