<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Filter;


/**
 * @method onFilter(Filter $control)
 */
interface Filter
{
    public function handleFilter($value = null): void;
}
