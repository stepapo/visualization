<?php

declare(strict_types=1);

namespace Stepapo\Visualization;

use Stepapo\Data\Column;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultFromSchematic;
use Stepapo\Utils\Attribute\Type;
use Stepapo\Utils\Schematic;


class Visualization extends Schematic
{
	public string $entityName;
	public string $defaultColumn;
	public string $defaultValue;
	public ?int $valueCollapse = null;
	public string $fontName;
	public int $fontSize = 16;
	public string $textColor = '#000000';
	public string $bgColor = '#ffffff';
	public string $gridlineColor = '#cccccc';
	public string $primaryColor;
	public int $height = 350;
	/** @var Column[] */ #[ArrayOfType(Column::class)] public array $columns;
	#[Type(VisualizationView::class), DefaultFromSchematic(VisualizationView::class)] public VisualizationView $view;
}