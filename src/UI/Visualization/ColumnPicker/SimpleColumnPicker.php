<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\ColumnPicker;

use Stepapo\Visualization\UI\Visualization\VisualizationControl;


/**
 * @property-read ColumnPickerTemplate $template
 * @method onPick(SimpleColumnPicker $control)
 */
class SimpleColumnPicker extends VisualizationControl implements ColumnPicker
{
    /** @persistent */
    public ?string $column = null;

    public array $onPick = [];


    public function render()
    {
        parent::render();
        $this->template->column = $this->column;
        $this->template->render($this->getView()->columnPickerTemplate);
    }


    public function handlePick(?string $column = null): void
    {
        $this->column = $column;
        if ($this->presenter->isAjax()) {
            $this->onPick($this);
            $this->redrawControl();
        }
    }
}
