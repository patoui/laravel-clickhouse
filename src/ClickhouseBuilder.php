<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

class ClickhouseBuilder extends Builder
{
    /**
     * @return array
     */
    public function getBindings(): array
    {
        $bindings = [];
        foreach ($this->wheres as $where) {
            if (!empty($where['value']) && !$where['value'] instanceof Expression) {
                // TODO: consider duplicate where column names
                $bindings[$where['column']] = $where['value'];
            }
        }
        // TODO: Consider overwrites from duplicate keys
        return $bindings;
    }
}
