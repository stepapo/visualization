<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Visualization;

use Stepapo\Utils\ConfigProcessor;
use Stepapo\Visualization\Column;
use Stepapo\Visualization\VisualizationView;
use Stepapo\Visualization\Filter;
use Stepapo\Visualization\LatteFilter;
use Stepapo\Visualization\Link;
use Stepapo\Visualization\Option;
use Stepapo\Visualization\Sort;
use Stepapo\Visualization\UI\Visualization\VisualizationControl;
use Stepapo\Visualization\UI\Visualization\VisualizationFactory;
use Stepapo\Visualization\UI\Visualization\Chart\SimpleChart;
use Stepapo\Visualization\UI\Visualization\Chart\Chart;
use Stepapo\Visualization\UI\Visualization\ColumnPicker\ColumnPicker;
use Stepapo\Visualization\UI\Visualization\ColumnPicker\SimpleColumnPicker;
use Stepapo\Visualization\UI\Visualization\Filter\SimpleFilter;
use Stepapo\Visualization\UI\Visualization\FilterList\SimpleFilterList;
use Stepapo\Visualization\UI\Visualization\FilterList\FilterList;
use Stepapo\Visualization\UI\MainComponent;
use Stepapo\Visualization\UI\Visualization\RowPicker\RowPicker;
use Stepapo\Visualization\UI\Visualization\RowPicker\SimpleRowPicker;
use Stepapo\Visualization\UI\Visualization\ValuePicker\ValuePicker;
use Stepapo\Visualization\Utils;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Nextras\Orm\Collection\ICollection;
use Contributte\ImageStorage\ImageStorage;


/**
 * @property-read VisualizationTemplate $template
 */
class Visualization extends VisualizationControl implements MainComponent
{
    private ?Column $rowColumn;

    private ?Column $columnColumn;

    private ?Column $valueColumn;

    private ICollection $collection;

    private ?ImageStorage $imageStorage;

    /** @var Column[]|null */
    private ?array $columns;

    private ?VisualizationFactory $factory;

    private array $filter = [];

    private ?VisualizationView $view;

    private ?int $valueCollapse;


    /**
     * @param Column[]|null $columns
     */
    public function __construct(
        ICollection $collection,
        ?ImageStorage $imageStorage = null,
        ?array $columns = null,
        ?VisualizationView $view = null,
        ?VisualizationFactory $factory = null,
        ?int $valueCollapse = null
    ) {
        $this->collection = $collection;
        $this->imageStorage = $imageStorage;
        $this->columns = $columns;
        $this->view = $view ?: VisualizationView::createDefault();
        $this->factory = $factory ?: VisualizationFactory::createDefault();
        $this->valueCollapse = $valueCollapse;
    }
    
    
    public static function createFromNeon(string $file, array $params = []): Visualization
    {
        $config = (array) Neon::decode(FileSystem::read($file));
        $parsedConfig = ConfigProcessor::replaceParams($config, $params);
        return self::createFromArray((array) $parsedConfig);
    }


    public static function createFromArray(array $config): Visualization
    {
        if (!isset($config['collection'])) {
            throw new InvalidArgumentException('Visualization collection has to be defined.');
        }
        $visualization = new self($config['collection']);
        if (array_key_exists('imageStorage', $config)) {
            $visualization->setImageStorage($config['imageStorage']);
        }
        if (array_key_exists('factory', $config)) {
            $visualization->setFactory(VisualizationFactory::createFromArray((array) $config['factory']));
        }
        if (array_key_exists('view', $config)) {
            $visualization->setView(VisualizationView::createFromArray((array) $config['view']));
        }
        if (array_key_exists('columns', $config)) {
            foreach ((array) $config['columns'] as $columnName => $columnConfig) {
                $visualization->addColumn(Column::createFromArray((array) $columnConfig, $columnName));
            }
        }
        if (array_key_exists('valueCollapse', $config)) {
            $visualization->setValueCollapse($config['valueCollapse']);
        }
        return $visualization;
    }


    public function loadState(array $params): void
    {
        parent::loadState($params);
        $this->rowColumn = $this->selectRowColumn();
        $this->columnColumn = $this->selectColumnColumn();
        $this->valueColumn = $this->selectValueColumn();
    }


    public function render()
    {
        parent::render();
        $this->filter()->sort();
        $this->template->render($this->view->visualizationTemplate);
    }



    public function getCollection(): ICollection
    {
        return $this->collection;
    }


    public function getImageStorage(): ?ImageStorage
    {
        return $this->imageStorage;
    }


    /** @return Column[]|null */
    public function getColumns(): ?array
    {
        return $this->columns;
    }


    public function getFactory(): VisualizationFactory
    {
        return $this->factory;
    }


    public function getView(): VisualizationView
    {
        return $this->view;
    }


    public function getFilter(): array
    {
        return $this->filter;
    }


    public function getRowColumn(): Column
    {
        return $this->rowColumn;
    }


    public function getColumnColumn(): Column
    {
        return $this->columnColumn;
    }


    public function getValueColumn(): Column
    {
        return $this->valueColumn;
    }


    public function getValueCollapse(): ?int
    {
        return $this->valueCollapse;
    }


    public function setCollection(ICollection $collection): Visualization
    {
        $this->collection = $collection;
        return $this;
    }


    public function setImageStorage(?ImageStorage $imageStorage): Visualization
    {
        $this->imageStorage = $imageStorage;
        return $this;
    }


    public function addColumn(Column $column): Visualization
    {
        $this->columns[$column->name] = $column;
        return $this;
    }


    /**
     * @param string|array|null $latteFilterArgs
     * @param string|array|null $linkArgs
     */
    public function createAndAddColumn(
        string $name,
        ?string $label = null,
        ?string $description = null,
        ?int $width = null,
        string $align = Column::ALIGN_LEFT,
        ?string $columnName = null,
        ?string $latteFilter = null,
        $latteFilterArgs = null,
        ?string $prepend = null,
        ?string $append = null,
        ?string $link = null,
        $linkArgs = null,
        ?string $valueTemplateFile = null,
        bool $sortable = false,
        bool $sortIsDefault = false,
        string $sortDefaultDirection = ICollection::ASC,
        ?array $filterOptions = null,
        ?string $filterPrompt = null,
        bool $hide = false,
        bool $cross = false,
        bool $aggregation = false,
        ?Chart $chart = null,
        ?string $class = null,
		?float $multiply = null,
		?float $add = null
    ): Column
    {
        $this->columns[$name] = new Column(
            $name,
            $description,
            $label,
            $width,
            $align,
            $columnName,
            $latteFilter ? new LatteFilter($latteFilter, (array) $latteFilterArgs) : null,
            $prepend,
            $append,
            $link ? new Link($link, (array) $linkArgs) : null,
            $valueTemplateFile,
            $sortable ? new Sort($sortIsDefault, $sortDefaultDirection) : null,
            $filterOptions ? new Filter($filterOptions, $filterPrompt) : null,
            $hide,
            $cross,
            $aggregation,
            $chart,
            $class,
			$multiply,
        );
        return $this->columns[$name];
    }


    public function setView(VisualizationView $view): Visualization
    {
        $this->view = $view;
        return $this;
    }


    public function createAndSetDefaultView(?string $name = null, bool $isDefault = false): VisualizationView
    {
        $this->view = VisualizationView::createDefault();
        return $this->view;
    }


    public function createAndSetView(
        ?string $visualizationTemplate = VisualizationView::DEFAULT_VIEW['visualizationTemplate'],
        ?string $chartTemplate = VisualizationView::DEFAULT_VIEW['chartTemplate'],
        ?string $filterListTemplate = VisualizationView::DEFAULT_VIEW['filterListTemplate'],
        ?string $filterTemplate = VisualizationView::DEFAULT_VIEW['filterTemplate'],
        ?string $rowPickerTemplate = VisualizationView::DEFAULT_VIEW['rowPickerTemplate'],
        ?string $columnPickerTemplate = VisualizationView::DEFAULT_VIEW['columnPickerTemplate'],
        ?string $valuePickerTemplate = VisualizationView::DEFAULT_VIEW['valuePickerTemplate'],
        ?string $valueTemplate = VisualizationView::DEFAULT_VIEW['valueTemplate']
    ): VisualizationView
    {
        $this->view = new VisualizationView(
            $visualizationTemplate,
            $chartTemplate,
            $filterListTemplate,
            $filterTemplate,
            $rowPickerTemplate,
            $columnPickerTemplate,
            $valuePickerTemplate,
            $valueTemplate
        );
        return $this->view;
    }


    public function setFactory(VisualizationFactory $factory): Visualization
    {
        $this->factory = $factory;
        return $this;
    }


    public function createAndSetFactory(
        ?string $tableClass = SimpleChart::class,
        ?string $filterListClass = SimpleFilterList::class,
        ?string $filterClass = SimpleFilter::class,
        ?string $rowPickerClass = SimpleRowPicker::class,
        ?string $columnPickerClass = SimpleColumnPicker::class,
        ?string $valuePickerClass = ValuePicker::class
    ): VisualizationFactory {
        $this->factory = new VisualizationFactory(
            $tableClass,
            $filterListClass,
            $filterClass,
            $rowPickerClass,
            $columnPickerClass,
            $valuePickerClass
        );
        return $this->factory;
    }


    public function setValueCollapse(?int $valueCollapse): Visualization
    {
        $this->valueCollapse = $valueCollapse;
        return $this;
    }


    public function createComponentChart(): Chart
    {
        return $this->getFactory()->createChart();
    }


    public function createComponentFilterList(): FilterList
    {
        $control = $this->getFactory()->createFilterList();
        $control->onFilter[] = function (FilterList $filterList) {
            $this->redrawControl();
        };
        return $control;
    }


    public function createComponentRowPicker(): RowPicker
    {
        $control = $this->getFactory()->createRowPicker();
        $control->onPick[] = function (RowPicker $rowPicker) {
            $this->getComponent('filterList')->getComponent('filter')->getComponent($rowPicker->row)->value = null;
            $this->redrawControl();
        };
        return $control;
    }


    public function createComponentColumnPicker(): ColumnPicker
    {
        $control = $this->getFactory()->createColumnPicker();
        $control->onPick[] = function (ColumnPicker $columnPicker) {
            $this->getComponent('filterList')->getComponent('filter')->getComponent($columnPicker->column)->value = null;
            $this->redrawControl();
        };
        return $control;
    }


    public function createComponentValuePicker(): ValuePicker
    {
        $control = $this->getFactory()->createValuePicker();
        $control->onPick[] = function (ValuePicker $valuePicker) {
            $this->redrawControl();
        };
        return $control;
    }


    private function filter(): Visualization
    {
        foreach ($this->columns as $column) {
            if (!$column->filter) {
                continue;
            }
            $value = $this->getComponent('filterList')->getComponent('filter')->getComponent($column->name)->value;
            if (!$value) {
                continue;
            }
            $this->filter[$column->name] = $value;
            if (!isset($column->filter->options[$value])) {
                $this->getComponent('filterList')->getComponent('filter')->getComponent($column->name)->value = null;
                continue;
            }
            if ($column->filter->options[$value] instanceof Option && $column->filter->options[$value]->condition) {
                $this->collection = $this->collection->findBy($column->filter->options[$value]->condition);
            } else {
                $this->collection = $this->collection->findBy([$column->name => $value]);
            }
        }
        return $this;
    }


    private function sort(): Visualization
    {
        if (!isset($this->getComponent('filterList')->getComponent('filter')->getComponent($this->getRowColumn()->name)->value)) {
            return $this;
        }
        $this->collection = $this->collection->orderBy($this->rowColumn->getNextrasName());
        return $this;
    }


    private function selectRowColumn()
    {
        if ($column = $this->getComponent('rowPicker')->row) {
            if (isset($this->columns[$column])) {
                return $this->columns[$column];
            }
        }

        return array_values($this->columns)[0];
    }


    private function selectColumnColumn()
    {
        if ($column = $this->getComponent('columnPicker')->column) {
            if (isset($this->columns[$column])) {
                return $this->columns[$column];
            }
        }

        return array_values($this->columns)[0];
    }


    private function selectValueColumn()
    {
        if ($column = $this->getComponent('valuePicker')->value) {
            if (isset($this->columns[$column])) {
                return $this->columns[$column];
            }
        }

        return array_values($this->columns)[0];
    }
}
