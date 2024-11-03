<?php

declare(strict_types=1);

namespace Stepapo\Visualization;

use Stepapo\Data\View;
use Stepapo\Utils\Schematic;


class VisualizationView extends Schematic implements View
{
	public const array DEFAULT_VIEW = [
		'visualizationTemplate' => __DIR__ . '/Control/Visualization/visualization.latte',
		'chartTemplate' => __DIR__ . '/Control/Chart/chart.latte',
		'filterListTemplate' => __DIR__ . '/../../data/src/Control/FilterList/list.latte',
		'filterTemplate' => __DIR__ . '/../../data/src/Control/Filter/list.latte',
		'rowPickerTemplate' => __DIR__ . '/Control/RowPicker/rowPicker.latte',
		'columnPickerTemplate' => __DIR__ . '/Control/ColumnPicker/columnPicker.latte',
		'valuePickerTemplate' => __DIR__ . '/Control/ValuePicker/valuePicker.latte',
	];
	public string $visualizationTemplate = self::DEFAULT_VIEW['visualizationTemplate'];
	public string $chartTemplate = self::DEFAULT_VIEW['chartTemplate'];
	public string $filterListTemplate = self::DEFAULT_VIEW['filterListTemplate'];
	public string $filterTemplate = self::DEFAULT_VIEW['filterTemplate'];
	public string $rowPickerTemplate = self::DEFAULT_VIEW['rowPickerTemplate'];
	public string $columnPickerTemplate = self::DEFAULT_VIEW['columnPickerTemplate'];
	public string $valuePickerTemplate = self::DEFAULT_VIEW['valuePickerTemplate'];


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		return parent::createFromArray(array_merge(self::DEFAULT_VIEW, (array) $config), $key, $skipDefaults, $parentKey);
	}
}