<?php

namespace Softspring\CmsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Data\DataImporter;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class CmsFixtures extends Fixture implements FixtureGroupInterface
{
    protected ContainerInterface $container;
    protected DataImporter $dataImporter;
    protected string $fixturesPath;

    public function __construct(ContainerInterface $container, DataImporter $dataImporter)
    {
        $this->container = $container;
        $this->dataImporter = $dataImporter;
        $this->fixturesPath = $this->container->getParameter('kernel.project_dir').'/cms/fixtures';
    }

    protected function readFixtures(): array
    {
        $contents = [];

        foreach (['contents', 'routes', 'blocks', 'menus'] as $type) {
            if (is_dir("$this->fixturesPath/$type")) {
                foreach ((new Finder())->in("$this->fixturesPath/$type")->files() as $contentFile) {
                    $data = Yaml::parseFile($contentFile->getRealPath());
                    $id = $contentFile->getFilenameWithoutExtension();
                    $contents[$type][$id] = $data;
                }
            }
        }

        if (is_dir("$this->fixturesPath/media")) {
            foreach ((new Finder())->in("$this->fixturesPath/media")->files()->name('*.json') as $mediaConfig) {
                $data = json_decode(file_get_contents($mediaConfig->getRealPath()), true);
                $id = $mediaConfig->getFilenameWithoutExtension();
                $contents['media'][$id] = [
                    'media' => $data,
                    'files' => [],
                ];

                $contents['media'][$id]['media']['versionFiles'] = array_map(fn ($name) => str_ireplace($this->fixturesPath.'/', '', $name), $contents['media'][$id]['media']['versionFiles']);

                foreach ((new Finder())->in("$this->fixturesPath/media")->files()->name("{$data['id']}.*")->notName('*.json') as $mediaFile) {
                    $contents['media'][$id]['files']["media/{$mediaFile->getFilename()}"] = [
                        'name' => $mediaFile->getFilename(),
                        'path' => "media/{$mediaFile->getFilename()}",
                        'size' => $mediaFile->getSize(),
                        'tmpPath' => $mediaFile->getRealPath(),
                    ];
                }
            }
        }

        return $contents;
    }

    public function load(ObjectManager $manager)
    {
        $manager->clear();
        $this->dataImporter->import($this->readFixtures(), ['version_origin' => ContentVersionInterface::ORIGIN_FIXTURE, 'auto_publish_version' => true]);
    }

    public static function getGroups(): array
    {
        return ['sfs_cms'];
    }
}
