<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

class MultiLetterIntegrationTest extends AbstractIntegrationTest
{
    public function testDoesNotSupportMultiLetterListMarkersByDefault(): void
    {
        $markdown = <<<MD
AA) foo
AB) bar
AC) baz
MD;
        $expectedHtml = <<<HTML
<p>AA) foo
AB) bar
AC) baz</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsMultiLetterListMarkersIfEnabledInConfiguration(): void
    {
        $markdown = <<<MD
AA) foo
AB) bar
AC) baz
MD;
        $expectedHtml = <<<HTML
<ol type="A" start="27">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testSupportsContinuingASingleLetterListWithMultiLetterListMarkers(): void
    {
        $markdown = <<<MD
Z) foo
AA) bar
AB) baz
MD;
        $expectedHtml = <<<HTML
<ol type="A" start="26">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testSupportsLowercaseMultiLetterListMarkers(): void
    {
        $markdown = <<<MD
aa) foo
ab) bar
ac) baz
MD;
        $expectedHtml = <<<HTML
<ol type="a" start="27">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testAllowsAtMost3CharactersForMultiLetterListMarkers(): void
    {
        $markdown = <<<MD
AAAA) foo
AAAB) bar
AAAC) baz
MD;
        $expectedHtml = <<<HTML
<p>AAAA) foo
AAAB) bar
AAAC) baz</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testDoesNotSupportMixingUppercaseAndLowercaseLetters(): void
    {
        $markdown = <<<MD
Aa) foo
Ab) bar
Ac) baz
MD;
        $expectedHtml = <<<HTML
<p>Aa) foo
Ab) bar
Ac) baz</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testPrefersRomanNumeralsOverMultiLetterAlphabeticNumerals(): void
    {
        $markdown = <<<MD
II) foo
III) bar
IV) baz
MD;
        $expectedHtml = <<<HTML
<ol type="I" start="2">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }

    public function testPrefersMultiLetterAlphabeticNumeralsOverRomanNumeralsWhenAlreadyInAnAlphabeticList(): void
    {
        $markdown = <<<MD
IH) foo
II) bar
IJ) baz
MD;
        $expectedHtml = <<<HTML
<ol type="A" start="242">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_multi_letter' => true,
        ]);
    }
}
