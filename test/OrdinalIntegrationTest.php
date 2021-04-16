<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;
use PHPUnit\Framework\TestCase;

class OrdinalIntegrationTest extends TestCase
{
    public function testDoesNotSupportAnOrdinalIndicatorByDefault(): void
    {
        $markdown = <<<MD
1º. foo
2º. bar
3º. baz
MD;
        $expectedHtml = <<<HTML
<p>1&#xBA;. foo
2&#xBA;. bar
3&#xBA;. baz</p>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown);
    }

    public function testSupportsAnOrdinalIndicatorIfEnabledInConfiguration(): void
    {
        $markdown = <<<MD
1º. foo
2º. bar
3º. baz
MD;
        $expectedHtml = <<<HTML
<ol class="ordinal">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_ordinal' => true,
        ]);
    }

    public function testAllowsOrdinalIndicatorsWithRomanNumerals(): void
    {
        $markdown = <<<MD
IIº. foo
IIIº. bar
IVº. baz
MD;
        $expectedHtml = <<<HTML
<ol type="I" start="2" class="ordinal">
  <li>foo</li>
  <li>bar</li>
  <li>baz</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_ordinal' => true,
        ]);
    }

    public function testStartsANewListWhenOrdinalIndicatorsAreIntroducedOrOmitted()
    {
        $markdown = <<<MD
1) First
1º) First again
2º) Second
1) Another first
MD;
        $expectedHtml = <<<HTML
<ol>
  <li>First</li>
</ol>
<ol class="ordinal">
  <li>First again</li>
  <li>Second</li>
</ol>
<ol>
  <li>Another first</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_ordinal' => true,
        ]);
    }

    public function testToleratesCharactersCommonlyMistakenForOrdinalIndicators(): void
    {
        $markdown = <<<MD
1°. degree sign
2˚. ring above
3ᵒ. modifier letter small o
4º. ordinal indicator
MD;
        $expectedHtml = <<<HTML
<ol class="ordinal">
  <li>degree sign</li>
  <li>ring above</li>
  <li>modifier letter small o</li>
  <li>ordinal indicator</li>
</ol>
HTML;

        $this->assertMarkdownIsConvertedTo($expectedHtml, $markdown, [
            'allow_ordinal' => true,
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
