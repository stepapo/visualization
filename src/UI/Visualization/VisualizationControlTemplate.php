<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\UI\DataControlTemplate;


abstract class VisualizationControlTemplate extends DataControlTemplate
{
    public ?Column $columnColumn;

    public ?Column $rowColumn;

    public ?Column $valueColumn;
}
