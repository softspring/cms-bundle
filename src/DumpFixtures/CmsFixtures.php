<?php

namespace Softspring\CmsBundle\DumpFixtures;

use Google\Cloud\Storage\StorageClient;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\MediaBundle\Model\MediaInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;

class CmsFixtures
{
    public static function dumpRoute(RouteInterface $route): string
    {
        $dump = [
            'route' => [
                'id' => $route->getId(),
                'type' => $route->getType(),
                'symfony_route' => $route->getSymfonyRoute(),
                'content' => null,
                'redirect_type' => $route->getRedirectType(),
                'redirect_url' => $route->getRedirectUrl(),
                'paths' => [],
            ],
        ];

        if ($route->getContent()) {
            $dump['route']['content'] = self::slugglify($route->getContent()->getName());
        }

        foreach ($route->getPaths() as $path) {
            $dump['route']['paths'][] = [
                'path' => $path->getPath(),
                'locale' => $path->getLocale(),
                'cache_ttl' => $path->getCacheTtl(),
            ];
        }

        $yaml = self::yaml($dump);
        $file = '/srv/cms/fixtures/routes/'.$route->getId().'.yaml';
        !is_dir('/srv/cms/fixtures/routes') && mkdir('/srv/cms/fixtures/routes', 0755, true);
        file_put_contents($file, $yaml);

        return $file;
    }

    public static function dumpMenu(MenuInterface $menu): string
    {
        $dump = [
            'menu' => [
                'type' => $menu->getType(),
                'name' => $menu->getName(),
                'items' => [],
            ],
        ];

        foreach ($menu->getItems() as $item) {
            $dump['menu']['items'][] = [
                'text' => $item->getText(),
                'route' => self::dumpData($item->getRoute()),
            ];
        }

        $yaml = self::yaml($dump);
        $file = '/srv/cms/fixtures/menus/'.self::slugglify($menu->getName()).'.yaml';
        !is_dir('/srv/cms/fixtures/menus') && mkdir('/srv/cms/fixtures/menus', 0755, true);
        file_put_contents($file, $yaml);

        return $file;
    }

    public static function dumpBlock(BlockInterface $block): string
    {
        $dump = [
            'block' => [
                'type' => $block->getType(),
                'name' => $block->getName(),
                'data' => self::dumpData($block->getData()),
            ],
        ];

        $yaml = self::yaml($dump);
        $file = '/srv/cms/fixtures/blocks/'.self::slugglify($block->getName()).'.yaml';
        !is_dir('/srv/cms/fixtures/blocks') && mkdir('/srv/cms/fixtures/blocks', 0755, true);
        file_put_contents($file, $yaml);

        return $file;
    }

    public static function dumpContent(ContentInterface $content, ?ContentVersionInterface $contentVersion, string $contentType): string
    {
        $dump = [
            $contentType => [
                'name' => $content->getName(),
                'extra' => $content->getExtraData(),
                'seo' => $content->getSeo(),
                'versions' => [
                    [
                        'layout' => $contentVersion->getLayout(),
                        'data' => self::dumpData($contentVersion->getData()),
                    ],
                ],
            ],
        ];

        $yaml = self::yaml($dump);
        $file = '/srv/cms/fixtures/contents/'.self::slugglify($content->getName()).'.yaml';
        !is_dir('/srv/cms/fixtures/contents') && mkdir('/srv/cms/fixtures/contents', 0755, true);
        file_put_contents($file, $yaml);

//        header('Content-type: text/plain');
//        echo $yaml;
//        exit();

        return $file;
    }

    private static function yaml(array $data): string
    {
        return Yaml::dump($data, 100, 4,
            Yaml::DUMP_OBJECT_AS_MAP
            & Yaml::DUMP_NULL_AS_TILDE
            & Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
            & Yaml::DUMP_OBJECT
            & Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE
            & Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    private static function slugglify(string $string): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug(strtolower($string));
    }

    public static function dumpData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::dumpData($value);
            }
        }

        if ($data instanceof RouteInterface) {
            return [
                '_reference' => 'route___'.$data->getId(),
            ];
        }

        if ($data instanceof BlockInterface) {
            return [
                '_reference' => 'block___'.self::slugglify($data->getName()),
            ];
        }

        if ($data instanceof MediaInterface) {
            !is_dir('/srv/cms/fixtures/media') && mkdir('/srv/cms/fixtures/media', 0755, true);

            $versionFiles = [];

            // TODO SET THIS IN A LOOP WITH ALL UPLOADED FILES (NOT ONLY _ORIGINAL FILE)
                $originalVersion = $data->getVersion('_original');
                $parts = explode('/', $originalVersion->getUrl(), 4);
                $mediaFileName = '/srv/cms/fixtures/media/'.$data->getId().([
                    'image/jpeg' => '.jpeg',
                    'image/png' => '.png',
                    'image/gif' => '.gif',
                    'image/webp' => '.webp',
                    'video/webm' => '.webm',
                ][$originalVersion->getFileMimeType()]??'');
                $storageClient = new StorageClient();
                $storageClient->bucket($parts[2])->object($parts[3])->downloadToFile($mediaFileName);
                $versionFiles['_original'] = $mediaFileName;

            file_put_contents('/srv/cms/fixtures/media/'.$data->getId().'.json', json_encode([
                'id' => $data->getId(),
                'type' => $data->getType(),
                'media_type' => $data->getMediaType(),
                'name' => $data->getName(),
                'description' => $data->getDescription(),
                'versionFiles' => $versionFiles,
            ], JSON_PRETTY_PRINT));

            return [
                '_reference' => 'media___'.$data->getId(),
            ];
        }

        return $data;
    }
}
