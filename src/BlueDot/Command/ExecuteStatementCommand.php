<?php

namespace BlueDot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteStatementCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bluedot:statement')
            ->setDescription('Executes a statement from the configuration')
            ->setHelp('After \'bluedot:statement\', put the statement name that you configured. For example \'php bluedot:statement simple.select.select_user\'. Only statements with no parameters to bound can be executed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();


    }
}