<?php

namespace Softspring\CmsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Dumper\Fixtures;
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

    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig)
    {
        parent::__construct();
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
                $dumpFile = Fixtures::dumpContent($content, $content->getVersions()->first() ?? null, $contentConfig, '/srv/cms/fixtures');

                $output->writeln(sprintf('Dumped "%s" %s content to %s', $content->getName(), $contentId, $dumpFile));
            }
        }
    }

    protected function dumpRoutes(OutputInterface $output)
    {
        /** @var RouteInterface $route */
        foreach ($this->em->getRepository(RouteInterface::class)->findAll() as $route) {
            $dumpFile = Fixtures::dumpRoute($route, '/srv/cms/fixtures');

            $output->writeln(sprintf('Dumped "%s" route to %s', $route->getId(), $dumpFile));
        }
    }

    protected function dumpMenus(OutputInterface $output)
    {
        /** @var MenuInterface $menu */
        foreach ($this->em->getRepository(MenuInterface::class)->findAll() as $menu) {
            $dumpFile = Fixtures::dumpMenu($menu, '/srv/cms/fixtures');

            $output->writeln(sprintf('Dumped "%s" menu to %s', $menu->getName(), $dumpFile));
        }
    }

    protected function dumpBlocks(OutputInterface $output)
    {
        /** @var BlockInterface $block */
        foreach ($this->em->getRepository(BlockInterface::class)->findAll() as $block) {
            $dumpFile = Fixtures::dumpBlock($block, '/srv/cms/fixtures');

            $output->writeln(sprintf('Dumped "%s" block to %s', $block->getName(), $dumpFile));
        }
    }
}
