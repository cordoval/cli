<?php

/*
 * This file is part of the puli/cli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Cli\Handler;

use Puli\Cli\Util\StringUtil;
use Puli\Discovery\Api\ResourceDiscovery;
use Puli\Repository\Api\ResourceRepository;
use RuntimeException;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\UI\Component\Table;
use Webmozart\Console\UI\Style\TableStyle;

/**
 * Handles the "find" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FindCommandHandler
{
    /**
     * @var ResourceRepository
     */
    private $repo;

    /**
     * @var ResourceDiscovery
     */
    private $discovery;

    /**
     * Creates the handler.
     *
     * @param ResourceRepository $repo      The resource repository.
     * @param ResourceDiscovery  $discovery The resource discovery.
     */
    public function __construct(ResourceRepository $repo, ResourceDiscovery $discovery)
    {
        $this->repo = $repo;
        $this->discovery = $discovery;
    }

    /**
     * Handles the "find" command.
     *
     * @param Args $args The console arguments.
     * @param IO   $io   The I/O.
     *
     * @return int The status code.
     */
    public function handle(Args $args, IO $io)
    {
        $criteria = array();

        if ($args->isArgumentSet('pattern')) {
            $criteria['pattern'] = $args->getArgument('pattern');
        }

        if ($args->isOptionSet('type')) {
            $criteria['shortClass'] = $args->getOption('type');
        }

        if ($args->isOptionSet('bound-to')) {
            $criteria['bindingType'] = $args->getOption('bound-to');
        }

        if (!$criteria) {
            throw new RuntimeException('No search criteria specified.');
        }

        return $this->listMatches($io, $criteria);
    }

    /**
     * Lists the matches for the given search criteria.
     *
     * @param IO    $io       The I/O.
     * @param array $criteria The array with the optional keys "pattern",
     *                        "shortClass" and "bindingType".
     *
     * @return int The status code.
     */
    private function listMatches(IO $io, array $criteria)
    {
        if (isset($criteria['pattern']) && isset($criteria['bindingType'])) {
            $matches = array_intersect_key(
                $this->findByPattern($criteria['pattern']),
                $this->findByBindingType($criteria['bindingType'])
            );
        } elseif (isset($criteria['pattern'])) {
            $matches = $this->findByPattern($criteria['pattern']);
        } elseif (isset($criteria['bindingType'])) {
            $matches = $this->findByBindingType($criteria['bindingType']);
        } else {
            $matches = $this->findByPattern('/*');
        }

        if (isset($criteria['shortClass'])) {
            $shortClass = $criteria['shortClass'];

            $matches = array_filter($matches, function ($value) use ($shortClass) {
                return $value === $shortClass;
            });
        }

        $this->printTable($io, $matches);

        return 0;
    }

    /**
     * Finds the resources for a given path pattern.
     *
     * @param string $pattern The path pattern.
     *
     * @return string[] An array of short resource class names indexed by
     *                  the resource path.
     */
    private function findByPattern($pattern)
    {
        $matches = array();

        if ('/' !== $pattern[0]) {
            $pattern = '/'.$pattern;
        }

        foreach ($this->repo->find($pattern) as $resource) {
            $matches[$resource->getPath()] = StringUtil::getShortClassName(get_class($resource));
        }

        return $matches;
    }

    /**
     * Finds the resources for a given binding type.
     *
     * @param string $typeName The type name.
     *
     * @return string[] An array of short resource class names indexed by
     *                  the resource path.
     */
    private function findByBindingType($typeName)
    {
        $matches = array();

        foreach ($this->discovery->findByType($typeName) as $binding) {
            foreach ($binding->getResources() as $resource) {
                $matches[$resource->getPath()] = StringUtil::getShortClassName(get_class($resource));
            }
        }

        ksort($matches);

        return $matches;
    }

    /**
     * Prints the given resources.
     *
     * @param IO       $io      The I/O.
     * @param string[] $matches An array of short resource class names indexed
     *                          by the resource path.
     */
    private function printTable(IO $io, array $matches)
    {
        $table = new Table(TableStyle::borderless());

        foreach ($matches as $path => $shortClass) {
            $table->addRow(array($shortClass, "<c1>$path</c1>"));
        }

        $table->render($io);
    }
}
