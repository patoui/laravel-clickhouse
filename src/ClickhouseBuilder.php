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

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values): bool
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            $this->cleanBindings(array_reduce($values, 'array_merge', []))
        );
    }

    /**
     * Remove all of the expressions from a list of bindings.
     *
     * @param  array  $bindings
     * @return array
     */
    protected function cleanBindings(array $bindings): array
    {
        return array_filter($bindings, static function ($binding) {
            return ! $binding instanceof Expression;
        });
    }
}
