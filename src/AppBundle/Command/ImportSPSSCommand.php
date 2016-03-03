<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSPSSCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('poq:importspss')
            ->setDescription('Import SPSS file')
            ->addArgument('file', InputArgument::REQUIRED)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting ImportSPSS process');
        $SPSS = new \SPSSReader($input->getArgument('file'));
        $this->getContainer()->get('spss.importer')->import($SPSS, $input->getArgument('file'));
    }
}