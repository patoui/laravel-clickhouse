<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use DateTimeInterface;
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

    /**
     * Add a "where month" statement to the query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @param  \DateTimeInterface|string|int|null  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            // modified, no leading zero
            $value = $value->format('n');
        }

        if (! $value instanceof Expression) {
            // modified, no leading zero
            $value = sprintf('%d', $value);
        }

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @param  \DateTimeInterface|string|int|null  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            // modified, no leading zero
            $value = $value->format('j');
        }

        if (! $value instanceof Expression) {
            // modified, no leading zero
            $value = sprintf('%d', $value);
        }

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }
}
