<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;
use PHPUnit\Framework\TestCase;

class MultiLetterIntegrationTest extends TestCase
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

    public function assertMarkdownIsConvertedTo(string $expectedHtml, string $markdown, ?array $config = null): void
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new FancyListsExtension());
        if ($config !== null) {
            $environment->setConfig($config);
        }

        $parser = new DocParser($environment);
        $renderer = new HtmlRenderer($environment);
        $actualOutput = $renderer->renderBlock($parser->parse($markdown));

        $this->assertXmlStringEqualsXmlString("<html>$expectedHtml</html>", "<html>$actualOutput</html>");
    }
}
