<?php

namespace Softspring\CmsBundle\Compiler;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;
use Symfony\Component\HttpFoundation\Request;

class ContentVersionCompiler extends AbstractVersionCompiler
{
    public function __construct(
        protected ContentVersionRenderer $contentVersionRender,
        protected CompiledDataManagerInterface $compiledDataManager,
        protected CmsHelper $cmsHelper,
        protected ?LoggerInterface $cmsLogger,
    ) {
    }

    /**
     * @return CompiledDataInterface[]
     * @throws CompileException
     */
    public function compileAll(VersionInterface $version): array
    {
        if (!$version instanceof ContentVersionInterface) {
            throw new CompileException('Version must be an instance of ContentVersionInterface');
        }

        $compiledDatas = [];

        foreach ($version->getContent()->getSites() as $site) {
            foreach ($version->getContent()->getLocales() ?? [] as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" content version for "%s" in "%s"', $version->getContent()->getName(), "$site", $locale));
                $request = IsolatedRequest::createIsolatedForContentRoute($version->getContent(), $locale, $site);
                $compiledDatas[] = $this->compileRequest($version, $request);
            }
        }

        return $compiledDatas;
    }

    /**
     * @throws CompileException
     */
    public function compileRequest(VersionInterface $version, Request $request, ?CompiledDataInterface $preCompiledData = null): CompiledDataInterface
    {
        if (!$version instanceof ContentVersionInterface) {
            throw new CompileException('Version must be an instance of ContentVersionInterface');
        }

        $compiledData = $this->compiledDataManager->createEntity();
        $compiledData->setKey($this->compiledDataManager->getCompileKeyFromRequest($version, $request));
        $compiledData->setVersion($version);

        try {
            if ($this->cmsHelper->compile()->contentSaveCompiled($version)) {
                $version->addCompiled($compiledData);
            }

            $renderErrors = new RenderErrorList();

            $compiledData = $this->compileRequestContainers($compiledData, $version, $request, $renderErrors, $preCompiledData);

            if ($compiledData->hasErrors()) {
                return $compiledData;
            }

            $compiledContainers = $compiledData->getDataPart('containers') ?? [];

            // compile data. Take into account that this method can return content and fill errors in the RenderErrorList
            $compiledCode = $this->contentVersionRender->render($version, $request, $renderErrors, $compiledContainers);
            $compiledData->setDataPart('content', $compiledCode);

            if ($renderErrors->hasErrors()) {
                $compiledData->setErrors(true);
                $compiledData->setDataPart('errors', $renderErrors->getErrorsAsArray());
            }
        } catch (RenderException $exception) {
            // if not content was set, set a default error content
            if (empty($compiledData->getDataPart('content'))) {
                $compiledData->setDataPart('content', '<!-- CONTENT_VERSION_COMPILE_ERROR -->');
            }

            $this->saveExceptionInCompiledData($compiledData, $exception);
        } catch (Exception $exception) {
            throw new CompileException('Error compiling content version request', 0, $exception);
        }

        return $compiledData;
    }

    /**
     * @throws CompileException
     */
    protected function compileRequestContainers(CompiledDataInterface $compiledData, ContentVersionInterface $version, Request $request, RenderErrorList $renderErrors, ?CompiledDataInterface $preCompiledData = null): CompiledDataInterface
    {
        try {
            $canSaveCompiledContainers = $this->cmsHelper->compile()->contentSaveCompiledContainers($version);
        } catch (Exception $exception) {
            throw new CompileException('Error determining if compiled containers can be saved', 0, $exception);
        }

        try {
            $compiledContainers = $preCompiledData?->getDataPart('containers');

            if (null === $compiledContainers) {
                $compiledContainers = $this->contentVersionRender->renderContainers($version, $request, $renderErrors);

                if ($renderErrors->hasErrors()) {
                    $compiledData->setErrors(true);
                    $compiledData->setDataPart('containers_errors', $renderErrors->getErrorsAsArray());
                }
            }

            $canSaveCompiledContainers && $compiledData->setDataPart('containers', $compiledContainers);
        } catch (RenderException $exception) {
            // if not content was set, set a default error content
            if (empty($compiledData->getDataPart('containers'))) {
                $canSaveCompiledContainers && $compiledData->setDataPart('containers', '<!-- CONTENT_VERSION_COMPILE_ERROR -->');
            }

            $this->saveExceptionInCompiledData($compiledData, $exception, 'containers_errors');
        } catch (Exception $exception) {
            throw new CompileException('Error compiling content version request', 0, $exception);
        }

        return $compiledData;
    }
}
