<?php

declare(strict_types=1);

namespace Stepapo\Visualization;

use Stepapo\Data\Column;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultFromConfig;
use Stepapo\Utils\Attribute\Type;
use Stepapo\Utils\Config;


class Visualization extends Config
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
	public ?int $width = null;
	/** @var Column[] */ #[ArrayOfType(Column::class)] public array $columns;
	#[Type(VisualizationView::class), DefaultFromConfig(VisualizationView::class)] public VisualizationView $view;
}