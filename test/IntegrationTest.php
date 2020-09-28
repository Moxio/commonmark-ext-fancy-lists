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
