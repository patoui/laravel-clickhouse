<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use InvalidArgumentException;
use Patoui\LaravelClickhouse\Traits\HasBindings;

class ClickhouseBuilder extends Builder
{
    use HasBindings;

    /**
     * Create a new query builder instance.
     *
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection,
        ?Grammar $grammar = null,
        ?Processor $processor = null,
    ) {
        parent::__construct($connection, $grammar, $processor);

        $this->grammar->resetBindingKeys();
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string  $columns
     */
    public function count($columns = null): int
    {
        return parent::count($columns ?: []);
    }

    public function getBindings(): array
    {
        return $this->flattenWithKeys($this->bindings);
    }

    /**
     * Insert a new record into the database.
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
     * Update a record in the database.
     */
    public function update(array $values): int
    {
        return $this->connection->update(
            $this->grammar->compileUpdate($this, $values),
            $this->cleanBindings(
                $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
            )
        );
    }

    /**
     * Remove all of the expressions from a list of bindings.
     */
    public function cleanBindings(array $bindings): array
    {
        return array_filter($bindings, static function ($binding) {
            return ! $binding instanceof Expression;
        });
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function addBinding($value, $type = 'where'): self
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $values = is_array($value) ? $value : [$value];

        foreach ($values as $value) {
            $this->bindings[$type][$this->nextBindingKey($value)] = $this->castBinding($value);
        }

        return $this;
    }
}
