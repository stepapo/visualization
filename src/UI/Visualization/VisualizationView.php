<?php

declare(strict_types=1);

namespace Stepapo\Visualization;


class VisualizationView implements View
{
    public const DEFAULT_VIEW = [
        'visualizationTemplate' => __DIR__ . '/Visualization/visualization.latte',
        'chartTemplate' => __DIR__ . '/Chart/chart.latte',
        'filterListTemplate' => __DIR__ . '/FilterList/list.latte',
        'filterTemplate' => __DIR__ . '/Filter/list.latte',
        'rowPickerTemplate' => __DIR__ . '/RowPicker/rowPicker.latte',
        'columnPickerTemplate' => __DIR__ . '/ColumnPicker/columnPicker.latte',
        'valuePickerTemplate' => __DIR__ . '/ValuePicker/valuePicker.latte',
        'valueTemplate' => __DIR__ . '/Value/value.latte'
    ];

    public string $visualizationTemplate;

    public string $chartTemplate;

    public string $filterListTemplate;

    public string $filterTemplate;

    public string $rowPickerTemplate;

    public string $columnPickerTemplate;

    public string $valuePickerTemplate;

    public string $valueTemplate;


    public function __construct(
        string $visualizationTemplate = self::DEFAULT_VIEW['visualizationTemplate'],
        string $chartTemplate = self::DEFAULT_VIEW['chartTemplate'],
        string $filterListTemplate = self::DEFAULT_VIEW['filterListTemplate'],
        string $filterTemplate = self::DEFAULT_VIEW['filterTemplate'],
        string $rowPickerTemplate = self::DEFAULT_VIEW['rowPickerTemplate'],
        string $columnPickerTemplate = self::DEFAULT_VIEW['columnPickerTemplate'],
        string $valuePickerTemplate = self::DEFAULT_VIEW['valuePickerTemplate'],
        string $valueTemplate = self::DEFAULT_VIEW['valueTemplate']
    ) {
        $this->visualizationTemplate = $visualizationTemplate;
        $this->chartTemplate = $chartTemplate;
        $this->filterListTemplate = $filterListTemplate;
        $this->filterTemplate = $filterTemplate;
        $this->rowPickerTemplate = $rowPickerTemplate;
        $this->columnPickerTemplate = $columnPickerTemplate;
        $this->valuePickerTemplate = $valuePickerTemplate;
        $this->valueTemplate = $valueTemplate;
    }
    
    
    public static function createFromArray(?array $config = null): VisualizationView
    {
        $view = new self();
        if (isset($config['visualizationTemplate'])) {
            $view->setVisualizationTemplate($config['visualizationTemplate']);
        }
        if (isset($config['chartTemplate'])) {
            $view->setChartTemplate($config['chartTemplate']);
        }
        if (isset($config['filterListTemplate'])) {
            $view->setFilterListTemplate($config['filterListTemplate']);
        }
        if (isset($config['filterTemplate'])) {
            $view->setFilterTemplate($config['filterTemplate']);
        }
        if (isset($config['rowPicker'])) {
            $view->setRowPickerTemplate($config['rowPicker']);
        }
        if (isset($config['columnPicker'])) {
            $view->setColumnPickerTemplate($config['columnPicker']);
        }
        if (isset($config['valuePicker'])) {
            $view->setValuePickerTemplate($config['valuePicker']);
        }
        if (isset($config['value'])) {
            $view->setValueTemplate($config['value']);
        }
        return $view;
    }


    public function setVisualizationTemplate(string $visualizationTemplate): VisualizationView
    {
        $this->visualizationTemplate = $visualizationTemplate;
        return $this;
    }


    public function setChartTemplate(string $chartTemplate): VisualizationView
    {
        $this->chartTemplate = $chartTemplate;
        return $this;
    }


    public function setFilterListTemplate(string $filterListTemplate): VisualizationView
    {
        $this->filterListTemplate = $filterListTemplate;
        return $this;
    }


    public function setFilterTemplate(string $filterTemplate): VisualizationView
    {
        $this->filterTemplate = $filterTemplate;
        return $this;
    }


    public function setPaginationTemplate(string $paginationTemplate): VisualizationView
    {
        $this->paginationTemplate = $paginationTemplate;
        return $this;
    }


    public function setSortingTemplate(string $sortingTemplate): VisualizationView
    {
        $this->sortingTemplate = $sortingTemplate;
        return $this;
    }


    public function setRowPickerTemplate(string $rowPickerTemplate): VisualizationView
    {
        $this->rowPickerTemplate = $rowPickerTemplate;
        return $this;
    }


    public function setColumnPickerTemplate(string $columnPickerTemplate): VisualizationView
    {
        $this->columnPickerTemplate = $columnPickerTemplate;
        return $this;
    }


    public function setValuePickerTemplate(string $valuePickerTemplate): VisualizationView
    {
        $this->valuePickerTemplate = $valuePickerTemplate;
        return $this;
    }


    public function setValueTemplate(string $valueTemplate): VisualizationView
    {
        $this->valueTemplate = $valueTemplate;
        return $this;
    }


    public static function createDefault(): VisualizationView
    {
        return new self();
    }
}
