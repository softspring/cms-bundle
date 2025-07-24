<?php

namespace Softspring\CmsBundle\Compiler;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorException;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;
use Softspring\CmsBundle\Render\SectionVersionRenderer;
use Symfony\Component\HttpFoundation\Request;

class SectionVersionCompiler extends AbstractVersionCompiler
{
    public function __construct(
        protected SectionVersionRenderer $sectionVersionRenderer,
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
        if (!$version instanceof SectionVersionInterface) {
            throw new CompileException('Version must be an instance of SectionVersionInterface');
        }

        $compiledDatas = [];

        foreach ($this->cmsHelper->config()->getSites() as $site) {
            foreach ($version->getSection()->getLocales() ?? [] as $locale) {
                $this->cmsLogger && $this->cmsLogger->debug(sprintf('Compiling "%s" section version for "%s" in "%s"', $version->getSection()->getName(), "$site", $locale));
                $request = IsolatedRequest::createIsolated($locale, $site);
                $compiledDatas[] = $this->compileRequest($version, $request);
            }
        }

        return $compiledDatas;
    }

    /**
     * @throws CompileException
     */
    public function compileRequest(VersionInterface $version, Request $request): CompiledDataInterface
    {
        if (!$version instanceof SectionVersionInterface) {
            throw new CompileException('Version must be an instance of SectionVersionInterface');
        }

        $compiledData = $this->compiledDataManager->createEntity();
        $compiledData->setKey($this->compiledDataManager->getCompileKeyFromRequest($version, $request));
        $compiledData->setVersion($version);

        try {
            if ($this->cmsHelper->compile()->sectionSaveCompiled($version)) {
                $version->addCompiled($compiledData);
            }

            // create structure for errors
            $renderErrors = new RenderErrorList();

            // compile data. Take into account that this method can return content and fill errors in the RenderErrorList
            $compiledCode = $this->sectionVersionRenderer->render($version, $request, $renderErrors);
            $compiledData->setDataPart('content', $compiledCode);

            if ($renderErrors->hasErrors()) {
                $compiledData->setErrors(true);
                $compiledData->setDataPart('errors', $renderErrors->getErrorsAsArray());
            }
        } catch (RenderException|RenderErrorException $exception) {
            // if not content was set, set a default error content
            if (empty($compiledData->getDataPart('content'))) {
                $compiledData->setDataPart('content', '<!-- SECTION_VERSION_COMPILE_ERROR -->');
            }

            $this->saveExceptionInCompiledData($compiledData, $exception);
        } catch (Exception $exception) {
            throw new CompileException('Error compiling section version request', 0, $exception);
        }

        return $compiledData;
    }
}
