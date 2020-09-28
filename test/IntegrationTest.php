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
A. foo
B. bar
C. baz
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
B. foo
C. bar
D. baz
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
I. foo
II. bar
III. baz
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
