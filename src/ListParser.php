<?php

/*
 * Modified version of the league/commonmark parser for fancy lists support.
 *
 * Modifications (c) Moxio.
 *
 * Original package is licensed under the BSD 3-Clause License. See original
 * copyright notice below:
 * ----------------------------------------------------------------------------
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moxio\CommonMark\Extension\FancyLists;

use League\CommonMark\Block\Element\ListBlock;
use League\CommonMark\Block\Element\ListData;
use League\CommonMark\Block\Element\ListItem;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;
use League\CommonMark\Util\RegexHelper;
use Romans\Filter\RomanToInt;
use Romans\Lexer\Exception as RomansLexerException;
use Romans\Parser\Exception as RomansParserException;

final class ListParser implements BlockParserInterface, ConfigurationAwareInterface
{
    /** @var ConfigurationInterface|null */
    private $config;

    /** @var string|null */
    private $listMarkerRegex;

    /** @var RomanToInt */
    private $romanToIntFilter;

    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->config = $configuration;
        $this->romanToIntFilter = new RomanToInt();
    }

    public function parse(ContextInterface $context, Cursor $cursor): bool
    {
        if ($cursor->isIndented() && !($context->getContainer() instanceof ListBlock)) {
            return false;
        }

        $indent = $cursor->getIndent();
        if ($indent >= 4) {
            return false;
        }

        $tmpCursor = clone $cursor;
        $tmpCursor->advanceToNextNonSpaceOrTab();
        $rest = $tmpCursor->getRemainder();

        $container = $context->getContainer();
        if (\preg_match($this->listMarkerRegex ?? $this->generateListMarkerRegex(), $rest) === 1) {
            $data = new ListData();
            $data->markerOffset = $indent;
            $data->type = ListBlock::TYPE_BULLET;
            $data->delimiter = null;
            $data->bulletChar = $rest[0];
            $markerLength = 1;
            $number = null;
            $numberingType = null;
            $hasOrdinalIndicator = false;
        } elseif (($matches = RegexHelper::matchAll('/^(\d{1,9}|[a-z]|[A-Z]|[ivxlcdm]+|[IVXLCDM]+|#)([\x{00BA}\x{00B0}\x{02DA}\x{1D52}]?)([.)])/u', $rest)) && (!($context->getContainer() instanceof Paragraph) || in_array($matches[1], ['1', 'a', 'A', 'i', 'I', '#'], true))) {
            $data = new ListData();
            $data->markerOffset = $indent;
            $data->type = ListBlock::TYPE_ORDERED;

            if ($matches[1] === '#') {
                $data->start = 1;
                if ($container instanceof ListBlock) {
                    $numberingType = $container->getListData()->bulletChar;
                } else {
                    $numberingType = null;
                }
            } else if (ctype_digit($matches[1])) {
                $data->start = (int)$matches[1];
                $numberingType = null;
            } else if (ctype_upper($matches[1])) {
                $withinRomanList = $container instanceof ListBlock && $container->getListData()->bulletChar === 'I';
                $withinAlphaList = $container instanceof ListBlock && $container->getListData()->bulletChar === 'A';
                try {
                    $parsedRomanNumber = $this->romanToIntFilter->filter($matches[1]);
                    $isValidRoman = true;
                } catch (RomansLexerException|RomansParserException $e) {
                    $isValidRoman = false;
                }
                $isValidAlpha = strlen($matches[1]) === 1;
                $preferRomanOverAlpha = $withinRomanList || (!$withinAlphaList && $matches[1] === 'I');

                if ($isValidRoman && (!$isValidAlpha || $preferRomanOverAlpha)) {
                    $data->start = $parsedRomanNumber;
                    $numberingType = 'I';
                } else if ($isValidAlpha) {
                    $data->start = ord($matches[1]) - ord('A') + 1;
                    $numberingType = 'A';
                } else {
                    return false;
                }
            } else {
                $withinRomanList = $container instanceof ListBlock && $container->getListData()->bulletChar === 'i';
                $withinAlphaList = $container instanceof ListBlock && $container->getListData()->bulletChar === 'a';
                try {
                    $parsedRomanNumber = $this->romanToIntFilter->filter(strtoupper($matches[1]));
                    $isValidRoman = true;
                } catch (RomansLexerException|RomansParserException $e) {
                    $isValidRoman = false;
                }
                $isValidAlpha = strlen($matches[1]) === 1;
                $preferRomanOverAlpha = $withinRomanList || (!$withinAlphaList && $matches[1] === 'i');

                if ($isValidRoman && (!$isValidAlpha || $preferRomanOverAlpha)) {
                    $data->start = $parsedRomanNumber;
                    $numberingType = 'i';
                } else if ($isValidAlpha) {
                    $data->start = ord($matches[1]) - ord('a') + 1;
                    $numberingType = 'a';
                } else {
                    return false;
                }
            }

            $data->delimiter = $matches[3];
            $data->bulletChar = $numberingType;
            $markerLength = \mb_strlen($matches[0]);
            $number = $matches[1];
            $hasOrdinalIndicator = $matches[2] !== "";
        } else {
            return false;
        }

        // Make sure we have spaces after
        $nextChar = $tmpCursor->peek($markerLength);
        if (!($nextChar === null || $nextChar === "\t" || $nextChar === ' ')) {
            return false;
        }

        // If the marker is a capital letter with a period, make sure it is followed by at least two spaces.
        // See https://pandoc.org/MANUAL.html#fn1
        if ($number !== null && $data->delimiter === "." && strlen($number) === 1 && ctype_upper($number)) {
            $nextChar = $tmpCursor->peek($markerLength + 1);
            if (!($nextChar === null || $nextChar === "\t" || $nextChar === ' ')) {
                return false;
            }
        }

        // If it interrupts paragraph, make sure first line isn't blank
        if ($container instanceof Paragraph && !RegexHelper::matchAt(RegexHelper::REGEX_NON_SPACE, $rest, $markerLength)) {
            return false;
        }

        // If the marker uses an ordinal indicator, ensure that this is enabled by config
        if ($hasOrdinalIndicator && !$this->config->get('allow_ordinal', false)) {
            return false;
        }

        // We've got a match! Advance offset and calculate padding
        $cursor->advanceToNextNonSpaceOrTab(); // to start of marker
        $cursor->advanceBy($markerLength, true); // to end of marker
        $data->padding = $this->calculateListMarkerPadding($cursor, $markerLength);

        // add the list if needed
        if (!($container instanceof ListBlock) || !$this->isCompatibleWithExistingList($container, $data, $hasOrdinalIndicator)) {
            $listBlock = new ListBlock($data);
            if ($numberingType !== null) {
                $listBlock->data['attributes']['type'] = $numberingType;
            }
            if ($hasOrdinalIndicator) {
                $listBlock->data['attributes']['class'] = 'ordinal';
            }

            $context->addBlock($listBlock);
        }

        // add the list item
        $context->addBlock(new ListItem($data));

        return true;
    }

    /**
     * @param Cursor $cursor
     * @param int    $markerLength
     *
     * @return int
     */
    private function calculateListMarkerPadding(Cursor $cursor, int $markerLength): int
    {
        $start = $cursor->saveState();
        $spacesStartCol = $cursor->getColumn();

        while ($cursor->getColumn() - $spacesStartCol < 5) {
            if (!$cursor->advanceBySpaceOrTab()) {
                break;
            }
        }

        $blankItem = $cursor->peek() === null;
        $spacesAfterMarker = $cursor->getColumn() - $spacesStartCol;

        if ($spacesAfterMarker >= 5 || $spacesAfterMarker < 1 || $blankItem) {
            $cursor->restoreState($start);
            $cursor->advanceBySpaceOrTab();

            return $markerLength + 1;
        }

        return $markerLength + $spacesAfterMarker;
    }

    private function generateListMarkerRegex(): string
    {
        // No configuration given - use the defaults
        if ($this->config === null) {
            return $this->listMarkerRegex = '/^[*+-]/';
        }

        $markers = $this->config->get('unordered_list_markers', ['*', '+', '-']);

        if (!\is_array($markers)) {
            throw new \RuntimeException('Invalid configuration option "unordered_list_markers": value must be an array of strings');
        }

        return $this->listMarkerRegex = '/^[' . \preg_quote(\implode('', $markers), '/') . ']/';
    }

    private function isCompatibleWithExistingList(ListBlock $container, ListData $newItemListData, bool $newItemHasOrdinalIndicator): bool
    {
        if (!$newItemListData->equals($container->getListData())) {
            return false;
        }

        $listClass = $container->data['attributes']['class'] ?? '';
        $listHasOrdinalIndicator = $listClass === 'ordinal';
        if ($listHasOrdinalIndicator !== $newItemHasOrdinalIndicator) {
            return false;
        }

        return true;
    }
}
