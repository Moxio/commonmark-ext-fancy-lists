<?php

declare(strict_types=1);

namespace Moxio\CommonMark\Extension\FancyLists;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

class FancyListsExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema("fancy_lists", Expect::structure([
            "allow_ordinal" => Expect::bool()->default(false),
            "allow_multi_letter" => Expect::bool()->default(false),
        ]));;
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(new ListBlockStartParser(), 300);
    }
}
