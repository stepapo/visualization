<?php

declare(strict_types=1);

namespace Stepapo\Visualization;


use Nette\InvalidArgumentException;


class Search
{
    public string $functionClass;

    public ?array $functionArgs;

    public ?string $placeholder;


    public function __construct(
        string $functionClass,
        ?array $functionArgs = null,
        ?string $placeholder = null
    ) {
        $this->functionClass = $functionClass;
        $this->functionArgs = $functionArgs;
        $this->placeholder = $placeholder;
    }


    public static function createFromArray(array $config): Search
    {
        if (!isset($config['functionClass'])) {
            throw new InvalidArgumentException('Search function has to be defined.');
        }
        return new self(
            $config['functionClass'],
            isset($config['functionArgs']) ? (array) $config['functionArgs'] : null,
            $config['placeholder'] ?? null
        );
    }
}
