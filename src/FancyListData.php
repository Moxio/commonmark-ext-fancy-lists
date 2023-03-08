<?php

declare(strict_types=1);

namespace Moxio\CommonMark\Extension\FancyLists;

use League\CommonMark\Extension\CommonMark\Node\Block\ListData;

class FancyListData extends ListData {
    public string $marker;
    public ?string $number;
    public ?string $numberingType;
    public bool $hasOrdinalIndicator;

    public function isCompatibleWith(FancyListData $existingListData): bool
    {
        return $this->equals($existingListData) &&
            $this->numberingType === $existingListData->numberingType &&
            $this->hasOrdinalIndicator === $existingListData->hasOrdinalIndicator;
    }
}
