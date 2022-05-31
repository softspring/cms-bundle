<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentVersionListener
{
    protected ContentRender $contentRender;
    protected RequestStack $requestStack;
    protected array $enabledLocales;

    public function __construct(ContentRender $contentRender, RequestStack $requestStack, array $enabledLocales)
    {
        $this->contentRender = $contentRender;
        $this->enabledLocales = $enabledLocales;
        $this->requestStack = $requestStack;
    }

    public function postLoad(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        $this->untransform($contentVersion, $event);
    }

    public function preUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event)
    {
        $this->transform($contentVersion, $event);
    }

    public function postUpdate(ContentVersionInterface $contentVersion, PreUpdateEventArgs $event)
    {
        $this->saveCompiled($contentVersion, $event);
    }

    public function prePersist(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        $this->saveCompiled($contentVersion, $event);
        $this->transform($contentVersion, $event);
    }

    protected function saveCompiled(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            // if no request provided, probably is running a command, fixtures, etc.
            return;
        }

        $originalLocale = $request->getLocale();

        $compiled = [];
        $compiledModules = [];
        foreach ($this->enabledLocales as $locale) {
            $request->setLocale($locale);
            // $compiled[$locale] = $this->contentRender->render($contentVersion);
            $compiledModules[$locale] = $this->contentRender->renderModules($contentVersion);
        }
        $contentVersion->setCompiled($compiled);
        $contentVersion->setCompiledModules($compiledModules);

        $request->setLocale($originalLocale);
    }

    protected function transform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->transformExtraDataValue($value, $event->getObjectManager());
                        }
                    }
                } else {
                    foreach ($module as $field => $value) {
                        $data[$layout][$m][$field] = $this->transformExtraDataValue($value, $event->getObjectManager());
                    }
                }
            }
        }
        $contentVersion->setData($data);
    }

    protected function untransform(ContentVersionInterface $contentVersion, LifecycleEventArgs $event)
    {
        if (!$contentVersion->getData()) {
            return;
        }

        $data = $contentVersion->getData();
        foreach ($data as $layout => $modules) {
            foreach ($modules as $m => $module) {
                if (isset($module['modules'])) {
                    foreach ($module['modules'] as $sm => $submodule) {
                        foreach ($submodule as $field => $value) {
                            $data[$layout][$m]['modules'][$sm][$field] = $this->untransformExtraDataValue($value, $event->getObjectManager());
                        }
                    }
                } else {
                    foreach ($module as $field => $value) {
                        $data[$layout][$m][$field] = $this->untransformExtraDataValue($value, $event->getObjectManager());
                    }
                }
            }
        }
        $contentVersion->setData($data);
    }

    protected function transformExtraDataValue($value, ObjectManager $objectManager)
    {
        if (is_object($value)) {
            try {
                return [
                    '_entity_class' => get_class($value),
                    '_entity_id' => $objectManager->getClassMetadata(get_class($value))->getIdentifierValues($value),
                ];
            } catch (MappingException $e) {
            }
        } elseif (is_array($value)) {
            foreach ($value as $key => $value2) {
                $value[$key] = $this->transformExtraDataValue($value2, $objectManager);
            }
        }

        return $value;
    }

    protected function untransformExtraDataValue($value, ObjectManager $objectManager)
    {
        if (is_array($value)) {
            if (isset($value['_entity_class'])) {
                $repo = $objectManager->getRepository($value['_entity_class']);

                return $repo->findOneBy($value['_entity_id']);
            } else {
                foreach ($value as $key => $value2) {
                    $value[$key] = $this->untransformExtraDataValue($value2, $objectManager);
                }
            }
        }

        return $value;
    }
}
