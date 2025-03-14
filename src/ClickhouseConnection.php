<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Closure;
use Generator;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;
use InvalidArgumentException;
use RuntimeException;
use SeasClick;
use Throwable;

class ClickhouseConnection extends BaseConnection
{
    /** @var SeasClick */
    private $db;

    /**
     * Connection constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->db = new SeasClick($config);

        $this->useDefaultPostProcessor();
        $this->useDefaultSchemaGrammar();
        $this->useDefaultQueryGrammar();
    }

    /**
     * Get SeasClick client
     */
    public function getClient(): SeasClick
    {
        return $this->db;
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param  Closure|Builder|string  $table
     * @param  string|null  $as
     * @return Builder
     */
    //    public function table($table, $as = null): Builder
    //    {
    //        $this->notImplementedException();
    //    }

    /**
     * Get a new query builder instance.
     */
    public function query(): ClickhouseBuilder
    {
        return new ClickhouseBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Get the query post processor used by the connection.
     */
    public function getDefaultPostProcessor(): Processor
    {
        return new ClickhouseProcessor;
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->db->select($query, $bindings);

        return array_shift($records);
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     */
    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->db->select($query, $bindings);
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     */
    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        $this->notImplementedException();
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string  $query  Query is table name
     * @param  array  $bindings
     */
    public function insert($query, $bindings = []): bool
    {
        [$keys, $values] = $this->parseBindings($bindings);

        return $this->db->insert($query, $keys, $values);
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public function update($query, $bindings = []): int
    {
        // TODO: remove hack and properly determine how many records will be updated
        return (int) $this->db->execute($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public function delete($query, $bindings = []): int
    {
        // TODO: determine how many records will be deleted
        return (int) $this->db->execute($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public function statement($query, $bindings = []): bool
    {
        return $this->db->execute($query, $bindings);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public function affectingStatement($query, $bindings = []): int
    {
        $this->notImplementedException();
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string  $query
     */
    public function unprepared($query): bool
    {
        return $this->db->execute($query);
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $this->notImplementedException();
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  callable|Closure  $callback
     * @param  int  $attempts
     * @return mixed
     *
     * @throws Throwable
     */
    public function transaction($callback, $attempts = 1)
    {
        $this->noTransactionException();
    }

    /**
     * Start a new database transaction.
     */
    public function beginTransaction(): void
    {
        $this->noTransactionException();
    }

    /**
     * Commit the active database transaction.
     */
    public function commit(): void
    {
        $this->noTransactionException();
    }

    /**
     * Rollback the active database transaction.
     *
     * @param  int|null  $toLevel
     */
    public function rollBack($toLevel = null): void
    {
        $this->noTransactionException();
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        $this->noTransactionException();
    }

    /**
     * Execute the given callback in "dry run" mode.
     */
    public function pretend(Closure $callback): array
    {
        $this->notImplementedException();
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar(): ClickhouseGrammar
    {
        return new ClickhouseGrammar;
    }

    /**
     * @param  array  $bindings  i.e. [['name' => 'John', 'user_id' => 321]]
     * @return array<array> [['name', 'user_id'], [['John', 321]]]
     */
    private function parseBindings(array $bindings): array
    {
        if (! $bindings) {
            return [[], []];
        }

        if (! is_string(current(array_keys($bindings)))) {
            throw new InvalidArgumentException(
                "Keys must be strings, i.e. ['name' => 'John', 'user_id' => 321]"
            );
        }

        return [array_keys($bindings), [array_values($bindings)]];
    }

    /**
     * Helper method to throw exception for not implemented functionality
     */
    private function notImplementedException(): void
    {
        throw new RuntimeException('Not currently implemented');
    }

    private function noTransactionException(): void
    {
        throw new RuntimeException('Clickhouse does not currently support transactions');
    }
}
