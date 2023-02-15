<?php

namespace Softspring\CmsBundle\Utils;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class ZipContent
{
    /**
     * @return array[]|false
     */
    public static function read(string $path, string $zipName)
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open("$path/$zipName")) {
            return false;
        }

        $contents = [
            'blocks' => [],
            'contents' => [],
            'media' => [],
            'menus' => [],
            'routes' => [],
        ];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $stat = $zip->statIndex($i);
            [$fileZipPath, $file] = explode('/', $stat['name'], 2);
            [$fileName, $fileExtension] = explode('.', $file, 2);

            switch ($fileZipPath) {
                case 'contents':
                    $fileContent = Yaml::parse(file_get_contents("zip://$path/$zipName#{$stat['name']}"));
                    $contents['contents'][explode('.yaml', $file, 2)[0]] = $fileContent;
                    break;

                case 'routes':
                    $fileContent = Yaml::parse(file_get_contents("zip://$path/$zipName#{$stat['name']}"));
                    $contents['routes'][explode('.yaml', $file, 2)[0]] = $fileContent;
                    break;

                case 'media':
                    if ('json' === $fileExtension) {
                        $contents['media'][$fileName]['media'] = json_decode(file_get_contents("zip://$path/$zipName#{$stat['name']}"), true);
                    } else {
                        $zip->extractTo(sys_get_temp_dir(), [$stat['name']]);

                        $contents['media'][$fileName]['files'][$stat['name']] = [
                            'name' => $file,
                            'path' => $stat['name'],
                            'size' => $stat['size'],
                            'tmpPath' => sys_get_temp_dir().'/'.$stat['name'],
                        ];
                    }
                    break;

                default:
                    break;
            }
        }

        return $contents;
    }

    public static function dump(string $path, string $zipName): string
    {
        // new zip
        $zip = new \ZipArchive();

        // get files
        $finder = new Finder();
        $finder->files()->in($path);

        // open zip
        if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
            throw new FileException('Zip file could not be created/opened.');
        }

        // loop files
        foreach ($finder as $file) {
            // add to zip
            $zip->addFile($file->getRealpath(), str_replace("$path/", '', $file->getRealpath()));
        }

        // close zip
        if (!$zip->close()) {
            throw new FileException('Zip file could not be closed.');
        }

        return $zipName;
    }

    public static function dumpResponse(string $path, string $zipName, bool $deleteAfterResponse = true): Response
    {
        self::dump($path, $zipName);

        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.pathinfo($zipName, PATHINFO_BASENAME).'"');
        $response->headers->set('Content-length', (string) filesize($zipName));

        $deleteAfterResponse && @unlink($zipName);

        return $response;
    }
}
