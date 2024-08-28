<?php

declare(strict_types=1);

namespace Stepapo\Visualization\Control\Chart;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\HasMany;
use Stepapo\Data\Control\DataControl;
use Stepapo\Visualization\Control\Visualization\VisualizationControl;
use Stepapo\Visualization\Visualization;


/**
 * @property-read ChartTemplate $template
 */
class ChartControl extends DataControl
{
	private array $items = [];
	private array $columnHeaderItems = [];


	public function __construct(
		private VisualizationControl $main,
		private Visualization $visualization,
		private ICollection $collection,
		private array $columns,
	) {}


	public function render(): void
    {
        $this->template->count = $this->collection->count();
        foreach ($this->collection as $item) {
			$rowValue = 0;
            $columnValue = $this->main->getColumnColumn()->name == 'month' ? $item->getMonth() : ($this->main->getColumnColumn()->name == 'day' ? $item->date->format('Y-m-d') : $item->getRawValue($this->main->getColumnColumn()->name));
            $this->items[$rowValue][$columnValue] = $item;
            $this->items[$rowValue]['total'] = ($items[$rowValue]['total'] ?? 0) + ($this->getValue($item, $this->main->getValueColumn()->columnName) !== null ? $this->getValue($item, $this->main->getValueColumn()->columnName)[0] : 0);
            if (!isset($columnHeaderItems[$columnValue])) {
                $this->columnHeaderItems[$columnValue] = $item;
            }
        }
        uasort($this->columnHeaderItems, fn($a, $b) =>
            strnatcmp(
                $this->main->getColumnColumn()->name == 'month' ? (string) $a->getMonth() : ($this->main->getColumnColumn()->name == 'day' ? $a->date->format('Y-m-d') : (string) $this->getValue($a, $this->main->getColumnColumn()->columnName)[0]),
                $this->main->getColumnColumn()->name == 'month' ? (string) $b->getMonth() : ($this->main->getColumnColumn()->name == 'day' ? $b->date->format('Y-m-d') : (string) $this->getValue($b, $this->main->getColumnColumn()->columnName)[0])
            )
        );
        uasort($this->items, fn($a, $b) => -($a['total'] <=> $b['total']));
		$this->template->table = $this->createTable();
		$this->template->options = $this->createOptions();
		$this->template->numberFormats = $this->createNumberFormats();
        $this->template->columnHeaderItems = $this->columnHeaderItems;
		$this->template->valueColumn = $this->main->getValueColumn();
        $this->template->items = $this->items;
		$this->template->visualization = $this->visualization;
		$this->template->chartType = $this->main->getColumnColumn()->chart?->type ?: $this->main->getValueColumn()->chart->type;
        $this->template->render($this->main->getView()->chartTemplate);
    }


    public function getValue(IEntity $entity, $columnName): ?array
    {
        $columnNames = explode('.', $columnName);
        $values = [$entity];
        foreach ($columnNames as $columnName) {
            $newValues = [];
            foreach ($values as $value) {
                if ($value instanceof HasMany) {
                    foreach ($value as $v) {
                        if (!isset($v->{$columnName})) {
                            return null;
                        }
                        $newValues[] = $v->{$columnName};
                    }
                } else {
                    if (!isset($value->{$columnName})) {
                        return null;
                    }
                    $newValues[] = $value->{$columnName};
                }
            }
            $values = $newValues;
        }
        return $values;
    }


	private function createTable(): array
	{
		return [
			'cols' => $this->createColumns(),
			'rows' => $this->createRows(),
		];
	}


	private function createColumns(): array
	{
		$columns = [];
		$columnColumn = $this->main->getColumnColumn();
		$valueColumn = $this->main->getValueColumn();
		if (in_array($columnColumn->name, ['year', 'month', 'day'])) {
			$columns[] = ['label' => 'Datum', 'type' => 'date'];
		} else {
			$columns[] = ['label' => $columnColumn->label, 'type' => 'string'];
		}
		if (count($this->items) > 1 || !$valueColumn->chart->series) {
			foreach ($this->items as $cols) {
				$columns[] = ['label' => 'Celkem', 'type' => 'number'];
				if (count($this->items) == 1 && in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) {
					$columns[] = ['label' => 'Přírůstek', 'type' => 'number'];
				}
			}
		} else {
			foreach ($valueColumn->chart->series as $serie) {
				$column = $this->columns[$serie];
				$columns[] = ['label' => $column->label, 'type' => 'number'];
			}
		}
		return $columns;
	}


	private function createRows(): array
	{
		$rows = [];
		$columnColumn = $this->main->getColumnColumn();
		$valueColumn = $this->main->getValueColumn();
		$value = [];
		foreach ($this->columnHeaderItems as $columnHeaderItem) {
			$row = [];
			if (in_array($columnColumn->name, ['year', 'month', 'day']) && isset($columnHeaderItem->year)) {
				$row[] = ['v' => 'Date(' . $columnHeaderItem->year . ', ' . (isset($columnHeaderItem->month) ? $columnHeaderItem->month - 1 : 0) . ', ' . ($columnHeaderItem->day ?? 1) . ')'];
			} else {
				$row[] = ['v' => $this->getValue($columnHeaderItem, $columnColumn->columnName)[0]];
			}
			if (count($this->items) > 1 || !$valueColumn->chart->series) {
				foreach ($this->items as $rowId => $cols) {
					if (in_array($columnColumn->name, ['year', 'month', 'day']) && $valueColumn->chart?->cumulative) {
						$v = isset($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)]) && $this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $valueColumn->columnName) ? ($value[$rowId] ?? $valueColumn->chart->base) + ($this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)], $valueColumn->columnName)[0]) : null;
						$value[$rowId] = $v == null ? ($value[$rowId] ?? $valueColumn->chart->base) : $v;
						$vo = isset($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)]) && $this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $valueColumn->columnName) ? $this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)], $valueColumn->columnName)[0] : null;
					} else {
						$v = isset($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)]) && $this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $valueColumn->columnName) ? $this->getValue($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)], $valueColumn->columnName)[0] : null;
					}
					if (isset($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)])) {
						$row[] = ['v' => $v * ($valueColumn->multiply ?: 1)];
					} else {
						$row[] = ['v' => null];
					}
					if (count($this->items) == 1 && in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) {
						if (isset($cols[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : $columnHeaderItem->getRawValue($columnColumn->name)])) {
							$row[] = ['v' => $vo * ($valueColumn->multiply ?: 1)];
						} else {
							$row[] = ['v' => null];
						}
					}
				}
			} else {
				$rowId = array_key_first($this->items);
				$item = array_values($this->items)[0];
				foreach ($valueColumn->chart->series as $serie) {
					$column = $this->columns[$serie];
					if (in_array($columnColumn->name, ['year', 'month', 'day']) && $column->chart?->cumulative) {
						$v = $value[$rowId] = $this->getValue($item[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $column->columnName) ? ($value[$rowId] ?? $column->chart->base) + $this->getValue($item[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $column->columnName)[0] : null;
					} else {
						$v = $this->getValue($item[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $column->columnName) ? $this->getValue($item[$columnColumn->name == 'month' ? $columnHeaderItem->getMonth() : ($columnColumn->name == 'day' ? $columnHeaderItem->date->format('Y-m-d') : $columnHeaderItem->getRawValue($columnColumn->name))], $column->columnName)[0] : null;
					}
					$row[] = ['v' => $v * ($valueColumn->multiply ?: 1)];
				}
			}
			$rows[] = [
				'c' => $row,
			];
		}
		return $rows;
	}


	private function createOptions(): array
	{
		$columnColumn = $this->main->getColumnColumn();
		$valueColumn = $this->main->getValueColumn();
		$options = [
			'interpolateNulls' => true,
			'fontName' => $this->visualization->fontName === 'system' ? "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" : $this->visualization->fontName,
			'fontSize' => $this->visualization->fontSize,
			'hAxis' => [
				'gridlines' => [
					'count' => min(ceil(count($this->columnHeaderItems) / ($columnColumn->name == 'month' && !$this->main->getComponent('filterList')->getComponent('filter')->getComponent('year')->value ? 12 : 1)), ($columnColumn->name == 'day' ? 10 : 15)),
					'color' => $this->visualization->bgColor,
				],
				'format' => $columnColumn->chart?->hFormat ? $columnColumn->chart->hFormat : null,
				'baselineColor' => $this->visualization->bgColor,
				'textStyle' => ['color' => $this->visualization->textColor],
			],
			'vAxes' => [
				[
					'gridlines' => [
						'count' => 5,
						'color' => $this->visualization->gridlineColor,
					],
					'minorGridlines' => [
						'count' => 4,
					],
					'format' => $valueColumn->chart?->vFormat ? $valueColumn->chart->vFormat : null,
					'viewWindowMode' => 'explicit',
					'viewWindow' => [
						'min' => $valueColumn->chart?->min !== null ? $valueColumn->chart->min : null,
						'max' => $valueColumn->chart?->max !== null ? $valueColumn->chart->max : null,
					],
					'baseline' => $valueColumn->chart?->base !== null ? $valueColumn->chart->base : 0,
					'textStyle' => ['color' => $this->visualization->textColor],
				],
			],
			'chartArea' => [
				'left' => $valueColumn->chart?->left !== null ? $valueColumn->chart->left : 0,
				'right' => $valueColumn->chart?->right !== null ? $valueColumn->chart->right : 0,
				'top' => count($this->items) > 1 || $valueColumn->chart->series || (in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) ? 25 : ($columnColumn->chart?->top !== null ? $columnColumn->chart->top : 0),
				'bottom' => $columnColumn->chart?->bottom !== null ? $columnColumn->chart->bottom : 0,
			],
			'legend' => [
				'position' => 'top',
				'textStyle' => ['color' => $this->visualization->textColor],
				'pagingTextStyle' => ['color' => $this->visualization->primaryColor],
				'scrollArrows' => ['activeColor' => $this->visualization->primaryColor],
			],
            'focusTarget' => 'category',
			'colors' => $valueColumn->chart?->colors ? $valueColumn->chart->colors : [$this->visualization->primaryColor],
			'backgroundColor' => $this->visualization->bgColor,
			'color' => $this->visualization->textColor,
            'isStacked' => $valueColumn->chart?->stacked ? $valueColumn->chart->stacked : null,
		];
		if ($valueColumn->chart->series && $valueColumn->chart->separateSeriesAxis) {
			$options['series'] = [];
			foreach ($valueColumn->chart->series as $key => $serie) {
				$column = $this->columns[$serie];
				$options['series'][] = ['targetAxisIndex' => $key, 'type' => $column->chart?->type ?: 'line'];
				if ($key === 0) {
					continue;
				}
				$vAxe = [
					'gridlines' => ['count' => 5, 'color' => $this->visualization->gridlineColor],
					'viewWindow' => [
						'min' => $column->chart?->min !== null ? $column->chart->min : null,
						'max' => $column->chart?->max !== null ? $column->chart->max : null,
					],
					'textStyle' => ['color' => $this->visualization->textColor],
				];
				if ($key > 1) {
					$vAxe['textPosition'] = 'none';
				}
				$options['vAxes'][] = $vAxe;
			}
		}
		if (count($this->items) == 1 && !$valueColumn->chart->series && in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) {
			$options['vAxes'][] = [
				'gridlines' => ['count' => 11],
				'format' => $valueColumn->chart?->vFormat ? $valueColumn->chart->vFormat : null,
				'textStyle' => ['color' => $this->visualization->textColor],
			];
			$options['series'] = [
				[
					'targetAxisIndex' => 0,
					'type' => 'line',
				],
				[
					'targetAxisIndex' => 1,
					'type' => 'bars',
				],
			];
		}
		return $options;
	}


	private function createNumberFormats(): array
	{
		return [];
	}
}
