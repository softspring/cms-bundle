<?php

namespace Softspring\CmsBundle\Data;

use Google\Cloud\Storage\StorageClient;
use Softspring\CmsBundle\Data\EntityTransformer\ContentEntityTransformerInterface;
use Softspring\CmsBundle\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\CmsBundle\Utils\YamlContent;

class DataExporter extends AbstractDataImportExport
{
    protected ReferencesRepository $referenceRepository;

    /**
     * @param EntityTransformerInterface[] $entityTransformers
     */
    public function __construct(iterable $entityTransformers)
    {
        parent::__construct($entityTransformers);
        $this->referenceRepository = new ReferencesRepository();
    }

    public function export(array $contents, array $options = []): void
    {
    }

    public function exportRoute(RouteInterface $route, string $path, array $options = []): string
    {
        !is_dir("$path/routes") && mkdir("$path/routes", 0755, true);

        $exportFile = YamlContent::save($this->getDataTransformer('routes', $route)->export($route), "$path/routes/{$route->getId()}.yaml");

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" route to %s', $route->getId(), $exportFile));

        return $exportFile;
    }

    public function exportMenu(MenuInterface $menu, string $path, array $options = []): string
    {
        !is_dir("$path/menus") && mkdir("$path/menus", 0755, true);

        $exportFile = YamlContent::save($this->getDataTransformer('menus', $menu)->export($menu), "$path/menus/".Slugger::lowerSlug($menu->getName()).'.yaml');

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" menu to %s', $menu->getId(), $exportFile));

        return $exportFile;
    }

    public function exportBlock(BlockInterface $block, string $path, array $options = []): string
    {
        !is_dir("$path/blocks") && mkdir("$path/blocks", 0755, true);

        $exportFile = YamlContent::save($this->getDataTransformer('blocks', $block)->export($block), "$path/blocks/".Slugger::lowerSlug($block->getName()).'.yaml');

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" block to %s', $block->getId(), $exportFile));

        return $exportFile;
    }

    public function exportContent(ContentInterface $content, ?ContentVersionInterface $contentVersion, array $contentTypeConfig, string $path, array $options = []): string
    {
        $contentType = $contentTypeConfig['_id'];

        !is_dir("$path/contents") && mkdir("$path/contents", 0755, true);
        $file = "$path/contents/".Slugger::lowerSlug($content->getName()).'.yaml';
        $files = [];
        /** @var ContentEntityTransformerInterface $transformer */
        $transformer = $this->getDataTransformer($contentType, $content);
        $filePath = YamlContent::save($transformer->export($content, $files, $contentVersion, $contentType), $file);

        foreach ($content->getRoutes() as $route) {
            self::exportRoute($route, $path);
        }

        foreach ($files as $fileName => $fileData) {
            switch ($fileData['@type']) {
                case 'json':
                    file_put_contents("$path/$fileName", json_encode($fileData['json'], JSON_PRETTY_PRINT));
                    break;

                case 'file':
                    switch ($fileData['@location']) {
                        case 'gcs':
                            $storageClient = new StorageClient();
                            !is_dir(dirname("$path/$fileName")) && mkdir(dirname("$path/$fileName"), 0755, true);
                            $storageClient->bucket($fileData['bucket'])->object($fileData['object'])->downloadToFile("$path/$fileName");
                            break;

                        default:
                            throw new \Exception('Not yet implemented');
                    }
                    break;

                default:
                    throw new \Exception('Not yet implemented');
            }
        }

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" %s content to %s', $content->getName(), $contentType, $filePath));

        return $filePath;
    }
}
