<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    // Testcase from https://spec.commonmark.org/0.29/#example-272
    public function testDoesNotAlterOrdinaryOrderedListSyntax(): void
    {
        $markdown = <<<MD
1. foo
2. bar
3) baz
MD;
        $expectedHtml = <<<HTML
<ol>
  <li>foo</li>
  <li>bar</li>
</ol>
<ol start="3">
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsLowercaseAlphabeticalNumbering(): void
    {
        $markdown = <<<MD
a. foo
b. bar
c. baz
MD;
        $expectedHtml = <<<HTML
<ol type="a">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsOffsetsForLowercaseAlphabeticalNumbering(): void
    {
        $markdown = <<<MD
b. foo
c. bar
d. baz
MD;
        $expectedHtml = <<<HTML
<ol type="a" start="2">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsUppercaseAlphabeticalNumbering(): void
    {
        $markdown = <<<MD
A) foo
B) bar
C) baz
MD;
        $expectedHtml = <<<HTML
<ol type="A">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsOffsetsForUppercaseAlphabeticalNumbering(): void
    {
        $markdown = <<<MD
B) foo
C) bar
D) baz
MD;
        $expectedHtml = <<<HTML
<ol type="A" start="2">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsLowercaseRomanNumbering(): void
    {
        $markdown = <<<MD
i. foo
ii. bar
iii. baz
MD;
        $expectedHtml = <<<HTML
<ol type="i">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsOffsetsForLowercaseRomanNumbering(): void
    {
        $markdown = <<<MD
iv. foo
v. bar
vi. baz
MD;
        $expectedHtml = <<<HTML
<ol type="i" start="4">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsUppercaseRomanNumbering(): void
    {
        $markdown = <<<MD
I) foo
II) bar
III) baz
MD;
        $expectedHtml = <<<HTML
<ol type="I">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsOffsetsForUppercaseRomanNumbering(): void
    {
        $markdown = <<<MD
XII. foo
XIII. bar
XIV. baz
MD;
        $expectedHtml = <<<HTML
<ol type="I" start="12">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testIgnoresInvalidRomanNumeralsAsListMarker(): void
    {
        $markdown = <<<MD
VV. foo
VVI. bar
VVII. baz
MD;
        $expectedHtml = <<<HTML
<p>VV. foo
VVI. bar
VVII. baz</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsHashAsListMarkerForSubsequentItems(): void
    {
        $markdown = <<<MD
1. foo
#. bar
#. baz
MD;
        $expectedHtml = <<<HTML
<ol>
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsHashAsListMarkerForInitialItem(): void
    {
        $markdown = <<<MD
#. foo
#. bar
#. baz
MD;
        $expectedHtml = <<<HTML
<ol>
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testAllowsFirstNumbersToInterruptParagraphs(): void
    {
        $markdown = <<<MD
I need to buy
a. new shoes
b. a coat
c. a plane ticket

I also need to buy
i. new shoes
ii. a coat
iii. a plane ticket
MD;
        $expectedHtml = <<<HTML
<p>I need to buy</p>
<ol type="a">
  <li>new shoes</li>
  <li>a coat</li>
  <li>a plane ticket</li>
</ol>
<p>I also need to buy</p>
<ol type="i">
  <li>new shoes</li>
  <li>a coat</li>
  <li>a plane ticket</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testDoesNotAllowSubsequentNumbersToInterruptParagraphs(): void
    {
        $markdown = <<<MD
I need to buy
b. new shoes
c. a coat
d. a plane ticket

I also need to buy
ii. new shoes
iii. a coat
iv. a plane ticket
MD;
        $expectedHtml = <<<HTML
<p>I need to buy
b. new shoes
c. a coat
d. a plane ticket</p>
<p>I also need to buy
ii. new shoes
iii. a coat
iv. a plane ticket</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testAllowsHashToInterruptParagraphs(): void
    {
        $markdown = <<<MD
I need to buy
#. new shoes
#. a coat
#. a plane ticket
MD;
        $expectedHtml = <<<HTML
<p>I need to buy</p>
<ol>
  <li>new shoes</li>
  <li>a coat</li>
  <li>a plane ticket</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsNestedLists()
    {
        $markdown = <<<MD
 9)  Ninth
10)  Tenth
11)  Eleventh
       i. subone
      ii. subtwo
     iii. subthree
MD;
        $expectedHtml = <<<HTML
<ol start="9">
  <li>Ninth</li>
  <li>Tenth</li>
  <li>Eleventh
<ol type="i">
  <li>subone</li>
  <li>subtwo</li>
  <li>subthree</li>
</ol>
</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testStartsANewListWhenADifferentTypeOfNumberingIsUsed()
    {
        $markdown = <<<MD
1) First
A) First again
i) Another first
ii) Second
MD;
        $expectedHtml = <<<HTML
<ol>
  <li>First</li>
</ol>
<ol type="A">
  <li>First again</li>
</ol>
<ol type="i">
  <li>Another first</li>
  <li>Second</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testStartsANewListWhenASequenceOfLettersIsNotAValidRomanNumeral()
    {
        $markdown = <<<MD
I) First
A) First again
MD;
        $expectedHtml = <<<HTML
<ol type="I">
  <li>First</li>
</ol>
<ol type="A">
  <li>First again</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testRequiresTwoSpacesAfterACapitalLetterAndAPeriod(): void
    {
        $markdown = <<<MD
B. Russell was an English philosopher.

I. Elba is an English actor.

I.  foo
II. bar

B.  foo
C.  bar
MD;
        $expectedHtml = <<<HTML
<p>B. Russell was an English philosopher.</p>
<p>I. Elba is an English actor.</p>
<ol type="I">
  <li>foo</li>
  <li>bar</li>
</ol>
<ol start="2" type="A">
  <li>foo</li>
  <li>bar</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function assertMarkdownIsConvertedTo($expectedHtml, $markdown): void
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new FancyListsExtension());

        $parser = new DocParser($environment);
        $renderer = new HtmlRenderer($environment);
        $actualOutput = $renderer->renderBlock($parser->parse($markdown));

        $this->assertXmlStringEqualsXmlString("<html>$expectedHtml</html>", "<html>$actualOutput</html>");
    }
}
