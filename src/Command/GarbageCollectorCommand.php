<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GarbageCollectorCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('sfs:cms:garbage-collector')
            ->setDescription('Garbage collector for CMS data')
            ->setHelp('This command allows you to clean up unused CMS data.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cleanupExpiredCompiledData();
        $output->writeln('Expired compiled data has been cleaned up.');

        $output->writeln('Garbage collection completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function cleanupExpiredCompiledData(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeQuery('DELETE FROM cms_compiled_data WHERE expires_at < UNIX_TIMESTAMP()');
    }
}
