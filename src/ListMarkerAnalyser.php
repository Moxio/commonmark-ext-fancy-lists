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

use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Util\RegexHelper;
use League\Config\ConfigurationInterface;
use Romans\Filter\RomanToInt;
use Romans\Lexer\Exception as RomansLexerException;
use Romans\Parser\Exception as RomansParserException;

class ListMarkerAnalyser
{
    private ConfigurationInterface $configuration;
    private RomanToInt $romanToIntFilter;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        $this->romanToIntFilter = new RomanToInt();
    }

    public function analyseFancyListItemMarker(int $indent, string $rest, bool $inParagraph, ?FancyListData $existingListData = null): ?FancyListData
    {
        if (\preg_match($this->generateListMarkerRegex(), $rest) === 1) {
            $data                      = new FancyListData();
            $data->markerOffset        = $indent;
            $data->type                = ListBlock::TYPE_BULLET;
            $data->delimiter           = null;
            $data->bulletChar          = $rest[0];
            $data->marker              = $rest[0];
            $data->number              = null;
            $data->numberingType       = null;
            $data->hasOrdinalIndicator = false;
        } elseif (($matches = RegexHelper::matchFirst('/^(\d{1,9}|[a-z]{1,3}|[A-Z]{1,3}|[ivxlcdm]+|[IVXLCDM]+|#)([\x{00BA}\x{00B0}\x{02DA}\x{1D52}]?)([.)])/u', $rest)) && (! $inParagraph || in_array($matches[1], ['1', 'a', 'A', 'i', 'I', '#'], true))) {
            if ($matches[1] === '#') {
                $start = 1;
                if ($existingListData) {
                    $numberingType = $existingListData->numberingType;
                } else {
                    $numberingType = null;
                }
            } else if (ctype_digit($matches[1])) {
                $start = (int)$matches[1];
                $numberingType = null;
            } else if (ctype_upper($matches[1])) {
                $withinRomanList = $existingListData !== null && $existingListData->numberingType === "I";
                $withinAlphaList = $existingListData !== null && $existingListData->numberingType === "A";
                try {
                    $parsedRomanNumber = $this->romanToIntFilter->filter($matches[1]);
                    $isValidRoman = true;
                } catch (RomansLexerException|RomansParserException $e) {
                    $isValidRoman = false;
                }
                $isValidAlpha = strlen($matches[1]) === 1 || $this->configuration->get('fancy_lists/allow_multi_letter');
                $preferRomanOverAlpha = $withinRomanList || (!$withinAlphaList && ($matches[1] === 'I' || strlen($matches[1]) > 1));

                if ($isValidRoman && (!$isValidAlpha || $preferRomanOverAlpha)) {
                    $start = $parsedRomanNumber;
                    $numberingType = 'I';
                } else if ($isValidAlpha) {
                    $start = NumberingUtils::convertAlphaMarkerToOrdinalNumber($matches[1]);
                    $numberingType = 'A';
                } else {
                    return null;
                }
            } else {
                $withinRomanList = $existingListData !== null && $existingListData->numberingType === "i";
                $withinAlphaList = $existingListData !== null && $existingListData->numberingType === "a";
                try {
                    $parsedRomanNumber = $this->romanToIntFilter->filter(strtoupper($matches[1]));
                    $isValidRoman = true;
                } catch (RomansLexerException|RomansParserException $e) {
                    $isValidRoman = false;
                }
                $isValidAlpha = strlen($matches[1]) === 1 || $this->configuration->get('fancy_lists/allow_multi_letter');
                $preferRomanOverAlpha = $withinRomanList || (!$withinAlphaList && ($matches[1] === 'i' || strlen($matches[1]) > 1));

                if ($isValidRoman && (!$isValidAlpha || $preferRomanOverAlpha)) {
                    $start = $parsedRomanNumber;
                    $numberingType = 'i';
                } else if ($isValidAlpha) {
                    $start = NumberingUtils::convertAlphaMarkerToOrdinalNumber($matches[1]);
                    $numberingType = 'a';
                } else {
                    return null;
                }
            }

            $data                      = new FancyListData();
            $data->markerOffset        = $indent;
            $data->type                = ListBlock::TYPE_ORDERED;
            $data->delimiter           = $matches[3];
            $data->start               = $start;
            $data->marker              = $matches[0];
            $data->number              = $matches[1];
            $data->numberingType       = $numberingType;
            $data->hasOrdinalIndicator = $matches[2] !== "";
        } else {
            return null;
        }

        // If the marker uses an ordinal indicator, ensure that this is enabled by config
        if ($data->hasOrdinalIndicator && !$this->configuration->get('fancy_lists/allow_ordinal')) {
            return null;
        }

        return $data;
    }

    private function generateListMarkerRegex(): string
    {
        $markers = $this->configuration->get('commonmark/unordered_list_markers');
        \assert(\is_array($markers));

        return '/^[' . \preg_quote(\implode('', $markers), '/') . ']/';
    }
}
