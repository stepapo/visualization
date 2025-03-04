<?php

declare(strict_types=1);

namespace Stepapo\Visualization\Control\Visualization;

use Nette\Application\BadRequestException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Model\IModel;
use Stepapo\Data\Column;
use Stepapo\Data\Control\DataControl;
use Stepapo\Data\Control\FilterList\FilterListControl;
use Stepapo\Data\Control\MainComponent;
use Stepapo\Data\Helper;
use Stepapo\Data\Option;
use Stepapo\Visualization\Control\Chart\ChartControl;
use Stepapo\Visualization\Control\ColumnPicker\ColumnPickerControl;
use Stepapo\Visualization\Control\ValuePicker\ValuePickerControl;
use Stepapo\Visualization\Visualization;
use Stepapo\Visualization\VisualizationView;


/**
 * @property-read VisualizationTemplate $template
 */
class VisualizationControl extends DataControl implements MainComponent
{
	private array $crossColumnNames;
	private ICollection $collection;


	public function __construct(
		private Visualization $visualization,
		private IModel $orm,
	) {}


	public function render(): void
	{
		$this->template->columnColumn = $this->getColumnColumn();
		$this->template->valueColumn = $this->getValueColumn();
		$this->filter()->sort();
		$this->template->render($this->getView()->visualizationTemplate);
	}


	public function getView(): VisualizationView
	{
		return $this->visualization->view;
	}


	public function createComponentChart(): ChartControl
	{
		return new ChartControl($this, $this->visualization, $this->getCollection(), $this->visualization->columns);
	}


	public function createComponentFilterList(): FilterListControl
	{
		$visibleColumns = array_filter(
			$this->visualization->columns,
			fn(Column $c) => $c->cross /*&& !$c->hide*/ && $c->name !== $this->getColumnColumn()->name
		);
		$control = new FilterListControl($this, $this->visualization->columns, $visibleColumns);
		$control->onFilter[] = function (FilterListControl $filterList) {
			$this->redrawControl();
		};
		return $control;
	}


	public function createComponentColumnPicker(): ColumnPickerControl
	{
		$control = new ColumnPickerControl($this, $this->visualization->columns, $this->visualization->defaultColumn);
		$control->onPick[] = function (ColumnPickerControl $columnPicker) {
			$this->getComponent('filterList')->getComponent('filter')->getComponent($columnPicker->column)->value = null;
			$this->redrawControl();
		};
		return $control;
	}


	public function createComponentValuePicker(): ValuePickerControl
	{
		$control = new ValuePickerControl($this, $this->visualization->columns, $this->visualization->defaultValue, $this->visualization->valueCollapse);
		$control->onPick[] = function (ValuePickerControl $valuePicker) {
			$this->redrawControl();
		};
		return $control;
	}


	private function filter(): VisualizationControl
	{
		foreach ($this->visualization->columns as $column) {
			if (!$column->filter || $column->name === $this->getColumnColumn()->name) {
				continue;
			}
			$value = $this->getComponent('filterList')->getComponent('filter')->getComponent($column->name)->value;
			if (!$value) {
				continue;
			}
			if (!isset($column->filter->options[$value])) {
				$this->getComponent('filterList')->getComponent('filter')->getComponent($column->name)->value = null;
				continue;
			}
			if ($column->filter->options[$value] instanceof Option && $column->filter->options[$value]->condition) {
				$this->collection = $this->getCollection()->findBy($column->filter->options[$value]->condition);
			} else {
				$this->collection = $this->getCollection()->findBy([$column->name => $value]);
			}
		}
		return $this;
	}


	private function sort(): VisualizationControl
	{
		if ($this->getColumnColumn()->name === 'month') {
			$this->collection = $this->getCollection()->orderBy('year');
		}
		$this->collection = $this->getCollection()->orderBy(Helper::getNextrasName($this->getColumnColumn()->columnName));
		return $this;
	}


	public function getColumnColumn(): Column
	{
		$column = $this->getComponent('columnPicker')->column;
		if (!isset($this->visualization->columns[$column])) {
			throw new BadRequestException;
		}
		return $this->visualization->columns[$column];
	}


	public function getValueColumn(): Column
	{
		$column = $this->getComponent('valuePicker')->value;
		if (!isset($this->visualization->columns[$column])) {
			throw new BadRequestException;
		}
		return $this->visualization->columns[$column];
	}


	private function getCrossColumnNames(): array
	{
		if (!isset($this->crossColumnNames)) {
			$this->crossColumnNames = array_map(
				fn(Column $column) => $column->name,
				array_filter($this->visualization->columns, fn(Column $column) => $column->cross)
			);
		}
		return $this->crossColumnNames;
	}


	public function getCollection(): ICollection
	{
		if (!isset($this->collection)) {
			$repositoryName = $this->visualization->entityName;
//			$row = $this->getComponent('rowPicker')->row ?? $this->visualization->defaultRow;
			$column = $this->getComponent('columnPicker')->column ?? $this->visualization->defaultColumn;
			$addBy = true;
			$addYear = true;
			foreach ($this->getCrossColumnNames() as $columnName) {
				$c = $this->visualization->columns[$columnName];
				$filterValue = $c->filter && $c->name !== $this->getColumnColumn()->name
					? $this->getComponent('filterList')->getComponent('filter')->getComponent($columnName)->value
					: null;
				if ((!$c->hide && $column === $columnName) || $filterValue) {
					if ($addBy) {
						$repositoryName .= 'By';
						$addBy = false;
					}
					if ($addYear) {
						if ($columnName === 'year') {
							$addYear = false;
						}
						if ($columnName === 'month') {
							$repositoryName .= 'Year';
						}
					}
					$repositoryName .= ucfirst($columnName);
				}
			}
			$repositoryName .= 'Repository';
			$this->collection = $this->orm->getRepositoryByName($repositoryName)->findAll();
		}
		return $this->collection;
	}
}
