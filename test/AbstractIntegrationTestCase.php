<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Renderer\HtmlRenderer;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected function assertMarkdownIsConvertedTo(string $expectedHtml, string $markdown, ?array $config = null): void
    {
        $environment = new Environment($config ?? []);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new FancyListsExtension());

        $parser = new MarkdownParser($environment);
        $renderer = new HtmlRenderer($environment);
        $actualOutput = $renderer->renderDocument($parser->parse($markdown));

        $this->assertXmlStringEqualsXmlString("<html>$expectedHtml</html>", "<html>$actualOutput</html>");
    }
}
