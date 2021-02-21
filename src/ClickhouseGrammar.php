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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereRaw(Builder $query, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereBasic(Builder $query, $where)
    {
//        $value = $this->parameter($where['value']);
        $value = $this->wrapInCurlyBraces($where);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereDate(Builder $query, $where)
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereTime(Builder $query, $where)
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array                              $where
     * @return string
     */
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Wrap in curly braces for clickhouse to recognize
     * @param array $where
     * @return string
     */
    private function wrapInCurlyBraces(array $where): string
    {
        return $this->isExpression($where['value'])
            ? $where['value']
            : '{' . $where['column'] . '}';
    }
}
