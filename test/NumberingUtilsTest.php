<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use Moxio\CommonMark\Extension\FancyLists\NumberingUtils;
use PHPUnit\Framework\TestCase;

class NumberingUtilsTest extends TestCase
{
    public function testCanConvertAlphaListMarkersToOrdinalNumber(): void
    {
        $this->assertSame(1, NumberingUtils::convertAlphaMarkerToOrdinalNumber("A"));
        $this->assertSame(2, NumberingUtils::convertAlphaMarkerToOrdinalNumber("B"));
        $this->assertSame(26, NumberingUtils::convertAlphaMarkerToOrdinalNumber("Z"));
        $this->assertSame(27, NumberingUtils::convertAlphaMarkerToOrdinalNumber("AA"));
        $this->assertSame(52, NumberingUtils::convertAlphaMarkerToOrdinalNumber("AZ"));
        $this->assertSame(53, NumberingUtils::convertAlphaMarkerToOrdinalNumber("BA"));
        $this->assertSame(677, NumberingUtils::convertAlphaMarkerToOrdinalNumber("ZA"));
        $this->assertSame(702, NumberingUtils::convertAlphaMarkerToOrdinalNumber("ZZ"));
        $this->assertSame(703, NumberingUtils::convertAlphaMarkerToOrdinalNumber("AAA"));
    }

    public function testCanAlsoConvertLowercaseAlphaListMarkers(): void
    {
        $this->assertSame(1, NumberingUtils::convertAlphaMarkerToOrdinalNumber("a"));
        $this->assertSame(2, NumberingUtils::convertAlphaMarkerToOrdinalNumber("b"));
        $this->assertSame(26, NumberingUtils::convertAlphaMarkerToOrdinalNumber("z"));
        $this->assertSame(27, NumberingUtils::convertAlphaMarkerToOrdinalNumber("aa"));
        $this->assertSame(52, NumberingUtils::convertAlphaMarkerToOrdinalNumber("az"));
        $this->assertSame(53, NumberingUtils::convertAlphaMarkerToOrdinalNumber("ba"));
        $this->assertSame(677, NumberingUtils::convertAlphaMarkerToOrdinalNumber("za"));
        $this->assertSame(702, NumberingUtils::convertAlphaMarkerToOrdinalNumber("zz"));
        $this->assertSame(703, NumberingUtils::convertAlphaMarkerToOrdinalNumber("aaa"));
    }
}
