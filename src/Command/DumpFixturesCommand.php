<?php

namespace Softspring\CmsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\DumpFixtures\CmsFixtures;
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
        $this->dumpContents($output);
        $this->dumpRoutes($output);
        $this->dumpMenus($output);

        return Command::SUCCESS;
    }

    protected function dumpContents(OutputInterface $output)
    {
        foreach ($this->cmsConfig->getContents() as $contentId => $content) {
            /** @var ContentInterface $content */
            foreach ($this->em->getRepository($content['entity_class'])->findAll() as $content) {
                $dumpFile = CmsFixtures::dumpContent($content, $content->getVersions()->first() ?? null, $contentId);

                $output->writeln(sprintf('Dumped "%s" %s content to %s', $content->getName(), $contentId, $dumpFile));
            }
        }
    }

    protected function dumpRoutes(OutputInterface $output)
    {
        /** @var RouteInterface $route */
        foreach ($this->em->getRepository(RouteInterface::class)->findAll() as $route) {
            $dumpFile = CmsFixtures::dumpRoute($route);

            $output->writeln(sprintf('Dumped "%s" route to %s', $route->getId(), $dumpFile));
        }
    }

    protected function dumpMenus(OutputInterface $output)
    {
        /** @var MenuInterface $menu */
        foreach ($this->em->getRepository(MenuInterface::class)->findAll() as $menu) {
            $dumpFile = CmsFixtures::dumpMenu($menu);

            $output->writeln(sprintf('Dumped "%s" menu to %s', $menu->getName(), $dumpFile));
        }
    }
}
