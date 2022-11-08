<?php

namespace Softspring\CmsBundle\Dumper\Utils;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;

class ZipDump
{
    public static function zipDump(string $path, string $zipName): string
    {
        // new zip
        $zip = new \ZipArchive();

        // get files
        $finder = new Finder();
        $finder->files()->in($path);

        // loop files
        foreach ($finder as $file) {
            // open zip
            if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
                throw new FileException('Zip file could not be created/opened.');
            }

            // add to zip
            $zip->addFile($file->getRealpath(), str_replace("$path/", '', $file->getRealpath()));

            // close zip
            if (!$zip->close()) {
                throw new FileException('Zip file could not be closed.');
            }
        }

        return $zipName;
    }

    public static function zipDumpResponse(string $path, string $zipName, bool $deleteAfterResponse = true): Response
    {
        self::zipDump($path, $zipName);

        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$zipName.'"');
        $response->headers->set('Content-length', filesize($zipName));

        $deleteAfterResponse && @unlink($zipName);

        return $response;
    }
}
