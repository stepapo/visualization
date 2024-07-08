<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\FilterList;

use Nette\Application\UI\Multiplier;
use Stepapo\Visualization\UI\Visualization\Filter\Filter;
use Stepapo\Visualization\UI\Visualization\VisualizationControl;


/**
 * @property-read FilterListTemplate $template
 * @method onFilter(SimpleFilterList $control)
 */
class SimpleFilterList extends VisualizationControl implements FilterList
{
    /** @persistent */
    public ?string $value = null;

    public array $onFilter = [];


    public function render()
    {
        parent::render();
        $this->template->render($this->getView()->filterListTemplate);
    }


    public function createComponentFilter()
    {
        return new Multiplier(function ($name): Filter {
            $control = $this->getFactory()->createFilter(
                $this->getColumns()[$name],
            );
            $control->onFilter[] = function (Filter $filter) {
                $this->onFilter($this);
                $this->redrawControl();
            };
            return $control;
        });
    }
}
