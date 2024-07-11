<?php

declare(strict_types=1);

namespace Stepapo\Visualization\Control\ColumnPicker;

use Stepapo\Data\Control\DataTemplate;


class ColumnPickerTemplate extends DataTemplate
{
	public ?string $column;
	public array $columns;
}
