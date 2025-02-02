<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Patoui\LaravelClickhouse\Traits\HasBindings;

class ClickhouseGrammar extends Grammar
{
    use HasBindings;

    /**
     * Compile an insert statement into SQL.
     */
    public function compileInsert(Builder $query, array $values): string
    {
        return $this->wrapTable($query->from);
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed  $value
     * @param  null  $key
     */
    public function parameter($value, $key = null): string
    {
        $parsedValue = $this->isExpression($value) ? $this->getValue($value) : $value;

        $param = '{' . $this->nextBindingKey($parsedValue) . '}';

        return is_string($parsedValue) ? $this->quoteString($param) : $param;
    }

    /**
     * Prepare the bindings for an update statement.
     *
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = Arr::except($bindings, ['select', 'join']);

        $values = Arr::flatten(array_map(fn ($value) => value($value), $values));

        $mergedBindings = array_values(array_merge(Arr::flatten($cleanBindings), $values));

        $keyedBindings = [];

        foreach ($mergedBindings as $value) {
            $keyedBindings[$this->nextBindingKey($value)] = $value;
        }

        return $keyedBindings;
    }

    /**
     * Compile a raw where clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereRaw(Builder $query, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values'], $where['column']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values'], $where['column']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotInRaw(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(', ', $where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereInRaw(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNull(Builder $query, $where)
    {
        return sprintf('isNull(%s)', $this->wrap($where['column']));
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return sprintf('isNotNull(%s)', $this->wrap($where['column']));
    }

    /**
     * Compile a "between" where clause.
     *
     * @param  array  $where
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
     * @param  array  $where
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
     * @param  array  $where
     * @return string
     */
    protected function whereDate(Builder $query, $where)
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereTime(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return sprintf(
            "formatDateTime(%s, '%%H:%%i:%%s') %s %s",
            $this->wrap($where['column']),
            $where['operator'],
            $value
        );
    }

    /**
     * Compile a "where day" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile an update statement without joins into SQL.
     *
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     */
    protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where): string
    {
        return "alter table {$table} update {$columns} {$where}";
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonSelector($value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'simpleJSONExtractString(' . $field . $path . ')';
    }

    /**
     * Wrap the given JSON path.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    protected function wrapJsonPath($value, $delimiter = '->')
    {
        $value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

        $jsonPath = collect(explode($delimiter, $value))
            ->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
            ->join('.');

        return sprintf("'%s'", $jsonPath);
    }

    /**
     * Wrap the given JSON path segment.
     *
     * @param  string  $segment
     * @return string
     */
    protected function wrapJsonPathSegment($segment)
    {
        if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
            $key = Str::beforeLast($segment, $parts[0]);

            if (! empty($key)) {
                return $key . $parts[0];
            }

            return $parts[0];
        }

        return $segment;
    }
}
