<?php

declare(strict_types=1);

namespace Stepapo\Visualization;


class Chart
{
    public const CHART_TYPE_LINE = 'LineChart';

    public const CHART_TYPE_AREA = 'AreaChart';

    public const CHART_TYPE_PIE = 'PieChart';

    public const CHART_TYPE_BAR = 'BarChart';

    public ?string $type;

    public ?float $min;

    public ?float $max;

    public ?int $left;

    public ?int $right;

    public ?int $top;

    public ?int $bottom;

    public ?string $hFormat;

    public ?string $vFormat;

    public bool $cumulative;

    public ?int $base;

    public ?array $series;

    public ?string $stacked;

    public ?array $colors;


    public function __construct(
        ?string $type = null,
        bool $cumulative = false,
        int $base = 0,
        ?float $min = null,
        ?float $max = null,
        ?int $left = null,
        ?int $right = null,
        ?int $top = null,
        ?int $bottom = null,
        ?string $hFormat = null,
        ?string $vFormat = null,
        ?array $series = null,
        ?string $stacked = null,
        ?array $colors = null,
        ?int $line = null
    ) {
        $this->type = $type;
        $this->cumulative = $cumulative;
        $this->base = $base;
        $this->min = $min;
        $this->max = $max;
        $this->left = $left;
        $this->right = $right;
        $this->top = $top;
        $this->bottom = $bottom;
        $this->hFormat = $hFormat;
        $this->vFormat = $vFormat;
        $this->series = $series;
        $this->stacked = $stacked;
        $this->colors = $colors;
    }


    public static function createFromArray(?array $config): Chart
    {
        return new self(
            array_key_exists('type', $config) ? $config['type'] : null,
            array_key_exists('cumulative', $config) ? $config['cumulative'] : false,
            array_key_exists('base', $config) ? $config['base'] : 0,
            array_key_exists('min', $config) ? $config['min'] : null,
            array_key_exists('max', $config) ? $config['max'] : null,
            array_key_exists('left', $config) ? $config['left'] : null,
            array_key_exists('right', $config) ? $config['right'] : null,
            array_key_exists('top', $config) ? $config['top'] : null,
            array_key_exists('bottom', $config) ? $config['bottom'] : null,
            array_key_exists('hFormat', $config) ? $config['hFormat'] : null,
            array_key_exists('vFormat', $config) ? $config['vFormat'] : null,
            array_key_exists('series', $config) ? $config['series'] : null,
            array_key_exists('stacked', $config) ? $config['stacked'] : null,
            array_key_exists('colors', $config) ? $config['colors'] : null,
        );
    }
}
