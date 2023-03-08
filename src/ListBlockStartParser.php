<?php

declare(strict_types=1);

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
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moxio\CommonMark\Extension\FancyLists;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

final class ListBlockStartParser implements BlockStartParserInterface, ConfigurationAwareInterface
{
    private ListMarkerAnalyser $listMarkerAnalyser;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->listMarkerAnalyser = new ListMarkerAnalyser($configuration);
    }

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $matched = $parserState->getLastMatchedBlockParser();
        $existingListData = ($matched instanceof ListBlockParser) ? $matched->getListData() : null;

        $listData = $this->parseList($cursor, $parserState->getParagraphContent() !== null, $existingListData);
        if ($listData === null) {
            return BlockStart::none();
        }

        $listItemParser = new ListItemParser($listData);

        // prepend the list block if needed
        if ($existingListData === null || !$listData->isCompatibleWith($existingListData)) {
            $listBlockParser = new ListBlockParser($listData);
            // We start out with assuming a list is tight. If we find a blank line, we set it to loose later.
            $listBlockParser->getBlock()->setTight(true);

            return BlockStart::of($listBlockParser, $listItemParser)->at($cursor);
        }

        return BlockStart::of($listItemParser)->at($cursor);
    }

    private function parseList(Cursor $cursor, bool $inParagraph, ?FancyListData $existingListData): ?FancyListData
    {
        $indent = $cursor->getIndent();

        $tmpCursor = clone $cursor;
        $tmpCursor->advanceToNextNonSpaceOrTab();
        $rest = $tmpCursor->getRemainder();

        $data = $this->listMarkerAnalyser->analyseFancyListItemMarker($indent, $rest, $inParagraph, $existingListData);
        if ($data === null) {
            return null;
        }

        $markerLength = \mb_strlen($data->marker);

        // Make sure we have spaces after
        $nextChar = $tmpCursor->peek($markerLength);
        if (! ($nextChar === null || $nextChar === "\t" || $nextChar === ' ')) {
            return null;
        }

        // If the marker is a capital letter with a period, make sure it is followed by at least two spaces.
        // See https://pandoc.org/MANUAL.html#fn1
        if ($data->number !== null && $data->delimiter === "." && strlen($data->number) === 1 && ctype_upper($data->number)) {
            $nextChar = $tmpCursor->peek($markerLength + 1);
            if (!($nextChar === null || $nextChar === "\t" || $nextChar === ' ')) {
                return null;
            }
        }

        // If it interrupts paragraph, make sure first line isn't blank
        if ($inParagraph && ! RegexHelper::matchAt(RegexHelper::REGEX_NON_SPACE, $rest, $markerLength)) {
            return null;
        }

        // We've got a match! Advance offset and calculate padding
        $cursor->advanceToNextNonSpaceOrTab(); // to start of marker
        $cursor->advanceBy($markerLength, true); // to end of marker
        $data->padding = self::calculateListMarkerPadding($cursor, $markerLength);

        return $data;
    }

    private static function calculateListMarkerPadding(Cursor $cursor, int $markerLength): int
    {
        $start          = $cursor->saveState();
        $spacesStartCol = $cursor->getColumn();

        while ($cursor->getColumn() - $spacesStartCol < 5) {
            if (! $cursor->advanceBySpaceOrTab()) {
                break;
            }
        }

        $blankItem         = $cursor->peek() === null;
        $spacesAfterMarker = $cursor->getColumn() - $spacesStartCol;

        if ($spacesAfterMarker >= 5 || $spacesAfterMarker < 1 || $blankItem) {
            $cursor->restoreState($start);
            $cursor->advanceBySpaceOrTab();

            return $markerLength + 1;
        }

        return $markerLength + $spacesAfterMarker;
    }
}
