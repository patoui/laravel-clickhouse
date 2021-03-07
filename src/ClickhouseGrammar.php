<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class ClickhouseGrammar extends Grammar
{
    /**
     * Compile a raw where clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereRaw(Builder $query, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value'], $where['token'] ?? null);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereIn(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereNotInRaw(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(', ', $where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereInRaw(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereBetween(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(reset($where['values']));

        $max = $this->parameter(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "between" where clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereBetweenColumns(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->wrap(reset($where['values']));

        $max = $this->wrap(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "where date" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereDate(Builder $query, $where)
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereTime(Builder $query, $where)
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param Builder $query
     * @param array   $where
     * @return string
     */
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param Builder $query
     * @param array   $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values): string
    {
        return $this->wrapTable($query->from);
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param array $values
     * @return string
     */
    public function parameterize(array $values): string
    {
        $parameters = [];
        $key_counts = [];
        foreach ($values as $key => $value) {
            $key_count        = $key_counts[$key] ?? 0;
            $parameters[]     = $this->parameter($value, $key);
            $key_counts[$key] = empty($key_counts[$key]) ? 1 : $key_counts[$key] + 1;
        }
        return implode(', ', $parameters);
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param mixed $value
     * @param null  $key
     * @return string
     */
    public function parameter($value, $key = null): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        $param = '{' . $key . '}';

        return is_string($value) ? "'{$param}'" : $param;
    }

    /**
     * Get an array of all the where clauses for the query.
     *
     * @param Builder $query
     * @return array
     */
    protected function compileWheresToArray($query): array
    {
        $compiled_wheres = [];
        foreach ($query->wheres as $key => $where) {
            $query->wheres[$key]['token'] = $where['token'] = $this->prepareKey($query, $where['column']);

            $compiled_wheres[] = $where['boolean'] . ' ' . $this->{"where{$where['type']}"}($query, $where);
        }

        return $compiled_wheres;
    }

    /**
     * Compile the columns for an update statement.
     *
     * @param Builder $query
     * @param array   $values
     * @return string
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return collect($values)->map(function ($value, $key) use ($query) {
            return $this->wrap($key) . ' = ' . $this->parameter($value, $this->prepareKey($query, $key));
        })->implode(', ');
    }

    /**
     * Compile an update statement without joins into SQL.
     *
     * @param Builder $query
     * @param string  $table
     * @param string  $columns
     * @param string  $where
     * @return string
     */
    protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where): string
    {
        return "alter table {$table} update {$columns} {$where}";
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param Builder $query
     * @param array   $values
     * @return string
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->compileUpdateColumns($query, $values);

        $where = $this->compileWheres($query);

        return trim(
            isset($query->joins)
                ? $this->compileUpdateWithJoins($query, $table, $columns, $where)
                : $this->compileUpdateWithoutJoins($query, $table, $columns, $where)
        );
    }

    /**
     * Prepare the bindings for an update statement.
     *
     * @param array $bindings
     * @param array $values
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        foreach ($bindings as $component => $component_bindings) {
            if (!$component_bindings) {
                continue;
            }
            foreach ($component_bindings as $key => $value) {
                if (!isset($values[$key])) {
                    $values[$key] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * Format named parameter key to avoid duplicates
     * @param Builder $query
     * @param string  $key
     * @return string
     */
    private function prepareKey(Builder $query, string $key): string
    {
        if (!isset($query->binding_count[$key])) {
            $query->binding_count[$key] = 0;
        }
        $key_count = ++$query->binding_count[$key];
        $key_index = $key_count - 1;
        $key       .= ($key_index > 0 ? $key_index : '');

        return $key;
    }
}
