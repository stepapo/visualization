<?php

declare(strict_types=1);

namespace Stepapo\Visualization\Control\Chart;

use Nextras\Orm\Entity\IEntity;
use Stepapo\Data\Column;
use Stepapo\Data\Control\DataTemplate;
use Stepapo\Visualization\Visualization;


class ChartTemplate extends DataTemplate
{
	/** @var IEntity[][] */ public array $items;
	/** @var IEntity[] */ public array $columnHeaderItems;
	public int $count;
	public array $table;
	public array $options;
	public array $numberFormats;
	public string $chartType;
	public Column $valueColumn;
	public Visualization $visualization;
}
