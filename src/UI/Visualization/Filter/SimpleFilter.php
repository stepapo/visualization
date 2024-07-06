<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Filter;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\UI\Visualization\VisualizationControl;


/**
 * @property-read FilterTemplate $template
 * @method onFilter(SimpleFilter $control)
 */
class SimpleFilter extends VisualizationControl implements Filter
{
    /** @persistent */
    public ?string $value = null;

    public array $onFilter = [];

    private Column $column;


    public function __construct(
        Column $column
    ) {
        $this->column = $column;
    }


    public function render()
    {
        parent::render();
        $this->template->column = $this->column;
        $this->template->value = $this->value;
        $this->template->render($this->getView()->filterTemplate);
    }


    public function handleFilter($value = null): void
    {
        $this->value = $value;
        if ($this->presenter->isAjax()) {
            $this->onFilter($this);
            $this->redrawControl();
        }
    }
}
