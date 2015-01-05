<?php

/*
 * This file is part of the puli/cli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Cli\Command;

use Puli\RepositoryManager\Discovery\DiscoveryManager;
use Puli\RepositoryManager\ManagerFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Command\CompositeCommand;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TypeRemoveCommand extends CompositeCommand
{
    protected function configure()
    {
        $this
            ->setName('type remove')
            ->setDescription('Remove a binding type')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the binding type')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $environment = ManagerFactory::createProjectEnvironment(getcwd());
        $packageManager = ManagerFactory::createPackageManager($environment);
        $discoveryManager = ManagerFactory::createDiscoveryManager($environment, $packageManager, $logger);

        return $this->removeBindingType($input->getArgument('name'), $discoveryManager);
    }

    private function removeBindingType($typeName, DiscoveryManager $discoveryManager)
    {
        $discoveryManager->removeBindingType($typeName);

        return 0;
    }
}