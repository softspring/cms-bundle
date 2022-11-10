<?php

namespace Softspring\CmsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Data\DataExporter;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpFixturesCommand extends Command
{
    protected static $defaultName = 'sfs:cms:dump-fixtures';

    protected DataExporter $dataExporter;
    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;

    public function __construct(DataExporter $dataExporter, EntityManagerInterface $em, CmsConfig $cmsConfig)
    {
        parent::__construct();
        $this->dataExporter = $dataExporter;
        $this->em = $em;
        $this->cmsConfig = $cmsConfig;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Removed fixtures/media files');
        $mediaPath = '/srv/cms/fixtures/media';
        array_map('unlink', glob("$mediaPath/*.*"));
        is_dir($mediaPath) && rmdir($mediaPath);

        $this->dumpContents($output);
        $this->dumpRoutes($output);
        $this->dumpMenus($output);
        $this->dumpBlocks($output);

        return Command::SUCCESS;
    }

    protected function dumpContents(OutputInterface $output)
    {
        foreach ($this->cmsConfig->getContents() as $contentId => $contentConfig) {
            /** @var ContentInterface $content */
            foreach ($this->em->getRepository($contentConfig['entity_class'])->findAll() as $content) {
                $this->dataExporter->exportContent($content, $content->getVersions()->first() ?? null, $contentConfig, '/srv/cms/fixtures', ['output' => $output]);
            }
        }
    }

    protected function dumpRoutes(OutputInterface $output)
    {
        /** @var RouteInterface $route */
        foreach ($this->em->getRepository(RouteInterface::class)->findAll() as $route) {
            $this->dataExporter->exportRoute($route, '/srv/cms/fixtures', ['output' => $output]);
        }
    }

    protected function dumpMenus(OutputInterface $output)
    {
        /** @var MenuInterface $menu */
        foreach ($this->em->getRepository(MenuInterface::class)->findAll() as $menu) {
            $this->dataExporter->exportMenu($menu, '/srv/cms/fixtures', ['output' => $output]);
        }
    }

    protected function dumpBlocks(OutputInterface $output)
    {
        /** @var BlockInterface $block */
        foreach ($this->em->getRepository(BlockInterface::class)->findAll() as $block) {
            $this->dataExporter->exportBlock($block, '/srv/cms/fixtures', ['output' => $output]);
        }
    }
}
