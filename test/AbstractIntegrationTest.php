<?php
namespace Moxio\CommonMark\Extension\FancyLists\Test;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Moxio\CommonMark\Extension\FancyLists\FancyListsExtension;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntegrationTest extends TestCase
{
    protected function assertMarkdownIsConvertedTo(string $expectedHtml, string $markdown, ?array $config = null): void
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
