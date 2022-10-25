<?php
namespace Moxio\CommonMark\Extension\FancyLists;

class NumberingUtils
{
    public static function convertAlphaMarkerToOrdinalNumber(string $alphaMarker): int
    {
        $lastLetterValue = ord(strtolower($alphaMarker[-1])) - ord('a') + 1;
        if (strlen($alphaMarker) > 1) {
            $prefixValue = self::convertAlphaMarkerToOrdinalNumber(substr($alphaMarker, 0, -1));
            return $prefixValue * 26 + $lastLetterValue;
        } else {
            return $lastLetterValue;
        }
    }
}
