<?php

namespace Softspring\CmsBundle\Dumper;

use Google\Cloud\Storage\StorageClient;
use Softspring\CmsBundle\Dumper\Model\BlockDumper;
use Softspring\CmsBundle\Dumper\Model\DumperInterface;
use Softspring\CmsBundle\Dumper\Model\MenuDumper;
use Softspring\CmsBundle\Dumper\Model\RouteDumper;
use Softspring\CmsBundle\Dumper\Utils\Slugger;
use Softspring\CmsBundle\Dumper\Utils\Yaml;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;

class Fixtures
{
    public static function dumpRoute(RouteInterface $route, string $path): string
    {
        !is_dir("$path/routes") && mkdir("$path/routes", 0755, true);

        return Yaml::save(RouteDumper::dump($route), "$path/routes/{$route->getId()}.yaml");
    }

    public static function dumpMenu(MenuInterface $menu, string $path): string
    {
        !is_dir("$path/menus") && mkdir("$path/menus", 0755, true);

        return Yaml::save(MenuDumper::dump($menu), "$path/menus/".Slugger::lowerSlug($menu->getName()).'.yaml');
    }

    public static function dumpBlock(BlockInterface $block, string $path): string
    {
        !is_dir("$path/blocks") && mkdir("$path/blocks", 0755, true);

        return Yaml::save(BlockDumper::dump($block), "$path/blocks/".Slugger::lowerSlug($block->getName()).'.yaml');
    }

    public static function dumpContent(ContentInterface $content, ?ContentVersionInterface $contentVersion, array $contentTypeConfig, string $path): string
    {
        $contentType = $contentTypeConfig['_id'];
        /** @var DumperInterface $dumperClass */
        $dumperClass = $contentTypeConfig['dumper_class'];

        !is_dir("$path/contents") && mkdir("$path/contents", 0755, true);
        $file = "$path/contents/".Slugger::lowerSlug($content->getName()).'.yaml';
        $files = [];
        $filePath = Yaml::save($dumperClass::dump($content, $files, $contentVersion, $contentType), $file);

        foreach ($content->getRoutes() as $route) {
            self::dumpRoute($route, $path);
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

        return $filePath;
    }
}
