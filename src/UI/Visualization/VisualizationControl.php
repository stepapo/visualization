<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\UI\DataControl;
use Stepapo\Visualization\UI\Visualization\Visualization\Visualization;


abstract class VisualizationControl extends DataControl
{   
    public function render()
    {
        parent::render();
        $this->template->rowColumn = $this->getRowColumn();
        $this->template->columnColumn = $this->getColumnColumn();
        $this->template->valueColumn = $this->getValueColumn();
    }


    public function getMainComponent(): ?Visualization
    {
        return $this->lookup(Visualization::class, false);
    }


    public function getRowColumn(): ?Column
    {
        return $this->getMainComponent()->getRowColumn();
    }


    public function getColumnColumn(): ?Column
    {
        return $this->getMainComponent()->getColumnColumn();
    }


    public function getValueColumn(): ?Column
    {
        return $this->getMainComponent()->getValueColumn();
    }
}
