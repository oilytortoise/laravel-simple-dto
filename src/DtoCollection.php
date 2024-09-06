<?php

namespace Oilytortoise\LaravelSimpleDto;

use Illuminate\Support\Collection;

/**
 * An object for storing a collection of DTOs.
 *
 * @author Oilytortoise
 */
abstract class DtoCollection extends Collection
{
    protected string $dtoClass;

    public function __construct(array $items = [])
    {
        $itemDtos = [];

        foreach ($items as $item) {
            if ($item instanceof $this->dtoClass) {
                $itemDtos[] = $item;
                continue;
            }
            $itemDtos[] = new $this->dtoClass($item);
        }

        parent::__construct($itemDtos);
    }
}
