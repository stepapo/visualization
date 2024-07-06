<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Filter;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\UI\Visualization\VisualizationControlTemplate;


class FilterTemplate extends VisualizationControlTemplate
{
    public Column $column;

    public ?string $value;
}
