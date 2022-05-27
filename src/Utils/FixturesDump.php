<?php

namespace Softspring\CmsBundle\Utils;

use Google\Cloud\Storage\StorageClient;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\ImageBundle\Model\ImageInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;

class FixturesDump
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

        if ($data instanceof ImageInterface) {
            !is_dir('/srv/cms/fixtures/images') && mkdir('/srv/cms/fixtures/images', 0755, true);
            $originalVersion = $data->getVersion('_original');

            $parts = explode('/', $originalVersion->getUrl(), 4);

            $imageFileName = '/srv/cms/fixtures/images/'.$data->getId().[
                'image/jpeg' => '.jpeg',
                'image/png' => '.png',
                'image/webp' => '.webp',
            ][$originalVersion->getFileMimeType()];

            $storageClient = new StorageClient();
            $storageClient->bucket($parts[2])->object($parts[3])->downloadToFile($imageFileName);

            file_put_contents('/srv/cms/fixtures/images/'.$data->getId().'.json', json_encode([
                'id' => $data->getId(),
                'type' => 'background',
                'name' => $data->getName(),
                'description' => $data->getDescription(),
                'file' => $imageFileName,
            ]));

            return [
                '_reference' => 'image___'.$data->getId(),
            ];
        }

        return $data;
    }
}
