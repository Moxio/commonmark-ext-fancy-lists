<?php
namespace Moxio\CommonMark\Extension\FancyLists;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

class FancyListsExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addBlockParser(new ListParser(), 100);
    }
}
