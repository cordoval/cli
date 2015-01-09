<?php

/*
 * This file is part of the webmozart/gitty package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class WrappedGrid
{
    const MIN_COLUMNS = 4;

    private $screenWidth;

    private $cells = array();

    public function __construct($screenWidth = null)
    {
        $this->screenWidth = $screenWidth ?: 80;
    }

    public function addCell($content)
    {
        $this->cells[] = $content;
    }

    public function render(OutputInterface $output)
    {
        $cellWidths = $this->getCellWidths();
        $minCellWidths = $this->getMinCellWidths();

        // Subtract one for separating space
        $maxWidth = floor($this->screenWidth / self::MIN_COLUMNS) - 1;

        $columnWidths = $this->getColumnWidths($cellWidths, $minCellWidths, $maxWidth);

        $cells = $this->wrapCells($this->cells, $columnWidths);

        $this->renderCells($output, $cells, $columnWidths);
    }

    private function wrapCells(array $cells, array $columnWidths)
    {
        $column = 0;
        $nbColumns = count($columnWidths);
        $rows = $currentRow = array();

        foreach ($cells as $cell) {
            if (0 === $column) {
                $rows[] = array();
                $currentRow = &$rows[count($rows) - 1];
            }

            $columnWidth = $columnWidths[$column];
            $currentRow[] = explode("\n", wordwrap($cell, $columnWidth));
            $column = ($column + 1) % $nbColumns;
        }

        // Fill the last row up
        $currentRow = array_pad($currentRow, $nbColumns, array());

        return $this->rearrangeCells($rows, $nbColumns);
    }

    private function rearrangeCells(array $rows, $nbColumns)
    {
        $cells = array();

        foreach ($rows as $row) {
            $columnsComplete = 0;

            while ($columnsComplete < $nbColumns) {
                foreach ($row as &$cellLines) {
                    $cells[] = $cellLines ? array_shift($cellLines) : '';

                    if (array() === $cellLines) {
                        $cellLines = null;
                        ++$columnsComplete;
                    }
                }
            }
        }

        return $cells;
    }

    private function renderCells(OutputInterface $output, array $cells, array $columnWidths)
    {
        $column = 0;
        $nbColumns = count($columnWidths);

        foreach ($cells as $cell) {
            if (0 !== $column) {
                $output->write(' ');
            }

            $columnWidth = $columnWidths[$column];

            $output->write(str_pad(wordwrap($cell, $columnWidth), $columnWidth, ' '));

            $column = ($column + 1) % $nbColumns;

            if (0 === $column) {
                $output->write("\n");
            }
        }

        // final line break
        if (0 !== $column) {
            $output->write("\n");
        }
    }

    private function getCellWidths()
    {
        $widths = array();

        foreach ($this->cells as $cell) {
            $widths[] = $this->getTextWidth($cell);
        }

        return $widths;
    }

    private function getMinCellWidths()
    {
        $minWidths = array();

        foreach ($this->cells as $cell) {
            $minWidths[] = $this->getMinTextWidth($cell);
        }

        return $minWidths;
    }

    private function getTextWidth($text)
    {
        $width = 0;

        foreach (explode("\n", $text) as $line) {
            $width = max($width, strlen($line));
        }

        return $width;
    }

    private function getMinTextWidth($text)
    {
        $spacePos = strpos($text, ' ');
        $nlPos = strpos($text, "\n");

        if (false === $spacePos && false === $nlPos) {
            return strlen($text);
        } elseif (false === $spacePos) {
            return $nlPos;
        } elseif (false === $nlPos) {
            return $spacePos;
        }

        return min($spacePos, $nlPos);
    }

    private function getColumnWidths(array $cellWidths, array $minCellWidths, $maxWidth)
    {
        $nbColumns = $this->calcInitialNumberOfColumns($cellWidths, $maxWidth);
        $widths = array($this->screenWidth);

        // Add count for separating spaces
        while ((array_sum($widths) + count($widths)) > $this->screenWidth) {
            $widths = $this->calcColumnWidths($cellWidths, $minCellWidths, $maxWidth, $nbColumns);
            $nbColumns--;
        }

        return $widths;
    }

    private function calcInitialNumberOfColumns(array $cellWidths, $maxWidth)
    {
        $totalWidth = 0;
        $nbColumns = 0;

        foreach ($cellWidths as $cellWidth) {
            // Add one for separating space
            $totalWidth += min($maxWidth, $cellWidth) + 1;

            if ($totalWidth > $this->screenWidth) {
                return $nbColumns;
            }

            $nbColumns++;
        }

        return $nbColumns;
    }

    private function calcColumnWidths(array $cellWidths, array $minCellWidths, $maxWidth, $nbColumns)
    {
        $widths = array_fill(0, $nbColumns, 0);
        $column = 0;

        foreach ($cellWidths as $i => $cellWidth) {
            $maxCellWidth = max($maxWidth, $minCellWidths[$i]);
            $widths[$column] = max($widths[$column], min($maxCellWidth, $cellWidth));
            $column = ($column + 1) % $nbColumns;
        }

        return $widths;
    }
}