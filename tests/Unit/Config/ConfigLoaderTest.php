<?php

namespace Softspring\CmsBundle\Test\Unit\Config;

use Softspring\CmsBundle\Config\ConfigLoader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class ConfigLoaderTest extends \PHPUnit\Framework\TestCase
{
    protected string $projectDir;
    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/sfs-cms-config-loader-'.bin2hex(random_bytes(6));
        $this->filesystem->mkdir($this->projectDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);
    }

    public function testSitesCanNotShareReservedRoutePathsOnSameHost(): void
    {
        $this->writeSiteConfig('default', <<<YAML
site:
  hosts:
    - domain: example.org
  robots:
    mode: static
YAML);
        $this->writeSiteConfig('blog', <<<YAML
site:
  hosts:
    - domain: example.org
  sitemaps:
    pages:
      url: robots.txt
YAML);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('reserved route "/robots.txt" for host "example.org" is configured by both site "default" (robots.txt) and site "blog" (sitemap "pages")');

        $this->createConfigLoader()->getSites($this->createContainer());
    }

    public function testSitesCanShareReservedRoutePathsOnDifferentHosts(): void
    {
        $this->writeSiteConfig('default', <<<YAML
site:
  hosts:
    - domain: example.org
  robots:
    mode: static
YAML);
        $this->writeSiteConfig('blog', <<<YAML
site:
  hosts:
    - domain: blog.example.org
  robots:
    mode: static
YAML);

        $sites = $this->createConfigLoader()->getSites($this->createContainer());

        $this->assertArrayHasKey('default', $sites);
        $this->assertArrayHasKey('blog', $sites);
    }

    public function testPathOnlySitesCanNotShareReservedRoutePaths(): void
    {
        $this->writeSiteConfig('docs', <<<YAML
site:
  paths:
    - path: /docs
  robots:
    mode: static
YAML);
        $this->writeSiteConfig('help', <<<YAML
site:
  paths:
    - path: /help
  robots:
    mode: static
YAML);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('reserved route "/robots.txt" for host "*"');

        $this->createConfigLoader()->getSites($this->createContainer());
    }

    protected function createConfigLoader(): ConfigLoader
    {
        return new ConfigLoader($this->createContainer(), ['cms']);
    }

    protected function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $this->projectDir);

        return $container;
    }

    protected function writeSiteConfig(string $site, string $config): void
    {
        $siteDir = "$this->projectDir/cms/sites/$site";
        $this->filesystem->mkdir($siteDir);
        file_put_contents("$siteDir/config.yaml", $config);
    }
}
