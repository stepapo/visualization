<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Chart;

use Stepapo\Visualization\UI\Visualization\VisualizationControlTemplate;
use Nextras\Orm\Entity\IEntity;


class ChartTemplate extends VisualizationControlTemplate
{
    /** @var IEntity[][] */
    public array $items;

    /** @var IEntity[] */
    public array $columnHeaderItems;
}
