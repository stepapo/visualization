<?php

declare(strict_types=1);

namespace Stepapo\Visualization\UI\Visualization\Value;

use Stepapo\Visualization\Column;
use Stepapo\Visualization\UI\Dataset\DatasetControlTemplate;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\HasMany;


class ValueTemplate extends DatasetControlTemplate
{
    public IEntity $entity;

    /** @var mixed */
    public $value;

    public Column $column;
}
