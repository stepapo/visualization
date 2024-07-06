<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Chart;

use Stepapo\Visualization\UI\Visualization\VisualizationControl;
use Stepapo\Visualization\UI\Visualization\Item\Item;
use Nette\Application\UI\Multiplier;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\HasMany;


/**
 * @property-read ChartTemplate $template
 */
class SimpleChart extends VisualizationControl implements Chart
{
	private array $items = [];

	private array $columnHeaderItems = [];


    public function render()
    {
        parent::render();
        $this->template->count = $this->getCollection()->count();
        foreach ($this->getCollection() as $item) {
            if ($this->getValue($item, $this->getRowColumn()->name)) {
                $rowValue = $item->getRawValue($this->getRowColumn()->name);
            } else {
                $rowValue = 0;
            }
            $columnValue = $this->getColumnColumn()->name == 'month' ? $item->getMonth() : ($this->getColumnColumn()->name == 'day' ? $item->date->format('Y-m-d') : $item->getRawValue($this->getColumnColumn()->name));
            $this->items[$rowValue][$columnValue] = $item;
            $this->items[$rowValue]['total'] = ($items[$rowValue]['total'] ?? 0) + ($this->getValue($item, $this->getValueColumn()->columnName) !== null ? $this->getValue($item, $this->getValueColumn()->columnName)[0] : 0);
            if (!isset($columnHeaderItems[$columnValue])) {
                $this->columnHeaderItems[$columnValue] = $item;
            }
        }
        uasort($this->columnHeaderItems, fn($a, $b) =>
            strnatcmp(
                $this->getColumnColumn()->name == 'month' ? (string) $a->getMonth() : ($this->getColumnColumn()->name == 'day' ? $a->date->format('Y-m-d') : (string) $this->getValue($a, $this->getColumnColumn()->columnName)[0]),
                $this->getColumnColumn()->name == 'month' ? (string) $b->getMonth() : ($this->getColumnColumn()->name == 'day' ? $b->date->format('Y-m-d') : (string) $this->getValue($b, $this->getColumnColumn()->columnName)[0])
            )
        );
        uasort($this->items, fn($a, $b) =>
			-($a['total'] <=> $b['total'])
        );
		$this->template->table = $this->createTable();
		$this->template->options = $this->createOptions();
		$this->template->numberFormats = $this->createNumberFormats();
        $this->template->columnHeaderItems = $this->columnHeaderItems;
        $this->template->items = $this->items;
		$this->template->chartType = $this->getColumnColumn()->chart?->type ?: $this->getValueColumn()->chart->type;
        $this->template->render($this->getView()->chartTemplate);
    }


    public function getValue(IEntity $entity, $columnName)
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


	private function createTable()
	{
		$table = [
			'cols' => $this->createColumns(),
			'rows' => $this->createRows(),
		];
		return $table;
	}


	private function createColumns()
	{
		$columns = [];
		$rowColumn = $this->getRowColumn();
		$columnColumn = $this->getColumnColumn();
		$valueColumn = $this->getValueColumn();

		if (in_array($columnColumn->name, ['year', 'month', 'day'])) {
			$columns[] = ['label' => 'Datum', 'type' => 'date'];
		} else {
			$columns[] = ['label' => $columnColumn->label, 'type' => 'string'];
		}

		if (count($this->items) > 1 || !$valueColumn->chart->series) {
			foreach ($this->items as $rowId => $cols) {
				$rowHeaderItem = array_values($cols)[0];
				$columns[] = ['label' => count($this->items) > 1 ? $this->getValue($rowHeaderItem, $rowColumn->columnName) : 'Celkem', 'type' => 'number'];
				if (count($this->items) == 1 && in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) {
					$columns[] = ['label' => 'Přírůstek', 'type' => 'number'];
				}
			}
		} else {
			$item = array_values($this->items)[0];
			foreach ($valueColumn->chart->series as $serie) {
				$column = $this->getColumns()[$serie];
				$columns[] = ['label' => $column->label, 'type' => 'number'];
			}
		}
		return $columns;
	}


	private function createRows()
	{
		$rows = [];
		$rowColumn = $this->getRowColumn();
		$columnColumn = $this->getColumnColumn();
		$valueColumn = $this->getValueColumn();

		$value = [];
		$filter = [];

		foreach ($this->columnHeaderItems as $columnHeaderItem) {
			$row = [];
			if (in_array($columnColumn->name, ['year', 'month', 'day']) && isset($columnHeaderItem->year)) {
				$row[] = ['v' => 'Date(' . $columnHeaderItem->year . ', ' . (isset($columnHeaderItem->month) ? $columnHeaderItem->month - 1 : 0) . ', ' . (isset($columnHeaderItem->day) ? $columnHeaderItem->day : 1) . ')'];
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
					$column = $this->getColumns()[$serie];
					if (in_array($columnColumn->name, ['year', 'month']) && $column->chart?->cumulative) {
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


	private function createOptions()
	{
		$rowColumn = $this->getRowColumn();
		$columnColumn = $this->getColumnColumn();
		$valueColumn = $this->getValueColumn();

		$options = [
			'interpolateNulls' => true,
			'fontName' => 'Trebuchet MS',
			'fontSize' => 16,
			'hAxis' => [
				'gridlines' => [
					'count' => min(count($this->columnHeaderItems) / ($columnColumn->name == 'month' && !$this->getMainComponent()->getComponent('filterList')->getComponent('filter')->getComponent('year')->value ? 12 : 1), ($columnColumn->name == 'day' ? 10 : 15)),
					'color' => '#ccc',
				],
				'format' => $columnColumn->chart?->hFormat ? $columnColumn->chart->hFormat : null,
				'baselineColor' => '#ffffff',
			],
			'vAxes' => [
				[
					'gridlines' => ['count' => 11],
					'format' => $valueColumn->chart?->vFormat ? $valueColumn->chart->vFormat : null,
					'viewWindowMode' => 'explicit',
					'viewWindow' => [
						'min' => $valueColumn->chart?->min !== null ? $valueColumn->chart->min : null,
						'max' => $valueColumn->chart?->max !== null ? $valueColumn->chart->max : null,
					],
					'baseline' => $valueColumn->chart?->base !== null ? $valueColumn->chart->base : 0,
				],
			],
			'textStyle' => ['color' => '#cccccc'],
			'chartArea' => [
				'backgroundColor' => [
					'stroke' => '#ccc',
					'strokeWidth' => 1,
					'fill' => '#fff',
				],
				'width' => '100%',
				'height' => '100%',
				'left' => $valueColumn->chart?->left !== null ? $valueColumn->chart->left : 0,
				'right' => $valueColumn->chart?->right !== null ? $valueColumn->chart->right : 0,
				'top' => count($this->items) > 1 || $valueColumn->chart->series || (in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) ? 25 : ($columnColumn->chart?->top !== null ? $columnColumn->chart->top : 0),
				'bottom' => $columnColumn->chart?->bottom !== null ? $columnColumn->chart->bottom : 0,
			],
			'legend' => 'top',
            'focusTarget' => 'category',
			'colors' => $valueColumn->chart?->colors !== null ? $valueColumn->chart->colors : null,
            'backgroundColor' => '#eee',
            'isStacked' => $valueColumn->chart?->stacked ? $valueColumn->chart->stacked : null,
		];

		if ($valueColumn->name == 'weather') {
			$options['series'] = [
				['targetAxisIndex' => 2, 'color' => '#dddddd', 'type' => 'area'],
				['targetAxisIndex' => 1, 'color' => '#3366cc', 'type' => 'area'],
				['targetAxisIndex' => 0, 'color' => '#dc3912'],
			];
			$options['vAxes'][] = [
				'gridlines' => ['count' => 11, 'color' => '#d0d0d0'],
				'minValue' => 0,
				'maxValue' => 1,
				'baselineColor' => '#d0d0d0',
				'viewWindow' => [
					'min' => 0,
				]
			];
			$options['vAxes'][] = [
				'gridlines' => ['count' => 11, 'color' => '#d0d0d0'],
				'textPosition' => 'none',
				'minValue' => 0,
				'maxValue' => 100,
				'baselineColor' => '#d0d0d0',
				'viewWindow' => [
					'min' => 0,
					'max' => 100,
				]
			];
		}

		if (count($this->items) == 1 && !$valueColumn->chart->series && in_array($columnColumn->name, ['year', 'month']) && $valueColumn->chart->cumulative) {
			$options['vAxes'][] = [
				'gridlines' => ['count' => 11],
				'format' => $valueColumn->chart?->vFormat ? $valueColumn->chart->vFormat : null,
				'textStyle' => ['color' => '#aaaaaa'],
			];
			$options['series'] = [
				[
					'targetAxisIndex' => 0,
					'type' => 'line',
				],
				[
					'targetAxisIndex' => 1,
					'type' => 'bars'
				],
			];
		}

		return $options;
	}


	private function createNumberFormats()
	{
		return [];
	}
}
