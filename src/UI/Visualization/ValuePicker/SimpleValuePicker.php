<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\ValuePicker;

use Stepapo\Visualization\UI\Visualization\VisualizationControl;


/**
 * @property-read ValuePickerTemplate $template
 * @method onPick(SimpleValuePicker $control)
 */
class SimpleValuePicker extends VisualizationControl implements ValuePicker
{
    /** @persistent */
    public ?string $value = null;

    public array $onPick = [];


    public function render()
    {
        parent::render();
        $this->template->value = $this->value;
        $this->template->valueCollapse = $this->getMainComponent()->getValueCollapse();
        $this->template->render($this->getView()->valuePickerTemplate);
    }


    public function handlePick(?string $value = null): void
    {
        $this->value = $value;
        if ($this->presenter->isAjax()) {
            $this->onPick($this);
            $this->redrawControl();
        }
    }
}
