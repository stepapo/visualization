<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI;

use Contributte\ImageStorage\ImageStorage;
use Stepapo\Visualization\Column;
use Stepapo\Visualization\Factory;
use Stepapo\Visualization\View;
use Nextras\Orm\Collection\ICollection;


interface MainComponent
{
    function getCollection(): ICollection;

    function getImageStorage(): ?ImageStorage;

    /** @return Column[]|null */
    function getColumns(): ?array;

    function getView(): View;

    function getFactory(): Factory;

    function getFilter(): array;
}
