<?php

declare(strict_types=1);

namespace Stepapo\Visualization\Control\Visualization;

use Stepapo\Data\Column;
use Stepapo\Data\Control\DataTemplate;


class VisualizationTemplate extends DataTemplate
{
	public ?Column $columnColumn;
	public ?Column $valueColumn;
}
