<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\Factory;
use Stepapo\Visualization\UI\Visualization\Chart\SimpleChart;
use Stepapo\Visualization\UI\Visualization\Chart\Chart;
use Stepapo\Visualization\UI\Visualization\ColumnPicker\ColumnPicker;
use Stepapo\Visualization\UI\Visualization\ColumnPicker\SimpleColumnPicker;
use Stepapo\Visualization\UI\Visualization\Filter\Filter;
use Stepapo\Visualization\UI\Visualization\Filter\SimpleFilter;
use Stepapo\Visualization\UI\Visualization\FilterList\FilterList;
use Stepapo\Visualization\UI\Visualization\FilterList\SimpleFilterList;
use Stepapo\Visualization\UI\Visualization\RowPicker\RowPicker;
use Stepapo\Visualization\UI\Visualization\RowPicker\SimpleRowPicker;
use Stepapo\Visualization\UI\Visualization\ValuePicker\SimpleValuePicker;
use Stepapo\Visualization\UI\Visualization\ValuePicker\ValuePicker;


class VisualizationFactory implements Factory
{
    public string $tableClass;

    public string $filterListClass;

    public string $filterClass;

    public string $rowPickerClass;

    public string $columnPickerClass;

    private string $valuePickerClass;


    public function __construct(
        string $tableClass = SimpleChart::class,
        string $filterListClass = SimpleFilterList::class,
        string $filterClass = SimpleFilter::class,
        string $rowPickerClass = SimpleRowPicker::class,
        string $columnPickerClass = SimpleColumnPicker::class,
        string $valuePickerClass = SimpleValuePicker::class
    ) {
        $this->tableClass = $tableClass;
        $this->filterListClass = $filterListClass;
        $this->filterClass = $filterClass;
        $this->rowPickerClass = $rowPickerClass;
        $this->columnPickerClass = $columnPickerClass;
        $this->valuePickerClass = $valuePickerClass;
    }


    public static function createFromArray(array $config): VisualizationFactory
    {
        $factory = new self();
        if (isset($config['tableClass'])) {
            $factory->setChartClass($config['tableClass']);
        }
        if (isset($config['filterListClass'])) {
            $factory->setFilterListClass($config['filterListClass']);
        }
        if (isset($config['filterClass'])) {
            $factory->setFilterClass($config['filterClass']);
        }
        if (isset($config['rowPickerClass'])) {
            $factory->setRowPickerClass($config['rowPickerClass']);
        }
        if (isset($config['columnPickerClass'])) {
            $factory->setColumnPickerClass($config['columnPickerClass']);
        }
        if (isset($config['valuePickerClass'])) {
            $factory->setValuePickerClass($config['valuePickerClass']);
        }
        return $factory;
    }


    public static function createDefault(): VisualizationFactory
    {
        return new self();
    }


    public function createChart(): Chart
    {
        return new $this->tableClass();
    }


    public function createFilterList(): FilterList
    {
        return new $this->filterListClass();
    }


    public function createFilter(Column $column): Filter
    {
        return new $this->filterClass($column);
    }


    public function createRowPicker(): RowPicker
    {
        return new $this->rowPickerClass();
    }


    public function createColumnPicker(): ColumnPicker
    {
        return new $this->columnPickerClass();
    }


    public function createValuePicker(): ValuePicker
    {
        return new $this->valuePickerClass();
    }


    public function setChartClass(string $tableClass): VisualizationFactory
    {
        $this->tableClass = $tableClass;
        return $this;
    }


    public function setFilterListClass(string $filterListClass): VisualizationFactory
    {
        $this->filterListClass = $filterListClass;
        return $this;
    }


    public function setFilterClass(string $filterClass): VisualizationFactory
    {
        $this->filterClass = $filterClass;
        return $this;
    }


    public function setRowPickerClass(string $rowPickerClass): VisualizationFactory
    {
        $this->rowPickerClass = $rowPickerClass;
        return $this;
    }


    public function setColumnPickerClass(string $columnPickerClass): VisualizationFactory
    {
        $this->columnPickerClass = $columnPickerClass;
        return $this;
    }


    public function setValuePickerClass(string $valuePickerClass): VisualizationFactory
    {
        $this->valuePickerClass = $valuePickerClass;
        return $this;
    }
}
