<?php

namespace Francerz\SqlBuilder\Expressions\Logical;

use ArrayAccess;
use Countable;
use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\Comparison\BetweenExpression;
use Francerz\SqlBuilder\Expressions\Comparison\ComparisonModes;
use Francerz\SqlBuilder\Expressions\Comparison\InExpression;
use Francerz\SqlBuilder\Expressions\Comparison\LikeExpression;
use Francerz\SqlBuilder\Expressions\Comparison\NullExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RegexpExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\Components\SqlValue;
use InvalidArgumentException;
use Iterator;

class ConditionList implements
    BooleanResultInterface,
    NegatableInterface,
    Countable,
    Iterator,
    ArrayAccess
{
    private $mode;
    private $conditions = [];

    private $negated = false;

    public function __construct($mode = ComparisonModes::COLUMN_VALUE)
    {
        $this->setMode($mode);
    }

    public function __clone()
    {
        $newConds = [];
        foreach ($this->conditions as &$c) {
            $newConds[] = clone $c;
        }
        $this->conditions = $newConds;
    }

    private function setMode($mode)
    {
        if (!in_array($mode, array(
            ComparisonModes::COLUMN_COLUMN,
            ComparisonModes::COLUMN_VALUE,
            ComparisonModes::VALUE_COLUMN,
            ComparisonModes::VALUE_VALUE
        ))) {
            throw new InvalidArgumentException('Invalid condition mode.');
        }
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function negate(bool $negate = true)
    {
        $this->negated = $negate;
    }
    public function isNegated(): bool
    {
        return $this->negated;
    }

    public function rewind()
    {
        reset($this->conditions);
    }
    public function current() : ConditionItem
    {
        return current($this->conditions);
    }
    public function next()
    {
        return next($this->conditions);
    }
    public function key()
    {
        return key($this->conditions);
    }
    public function valid() : bool
    {
        return key($this->conditions) !== null;
    }
    public function count()
    {
        return count($this->conditions);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->conditions);
    }

    public function offsetGet($offset)
    {
        return $this->conditions[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof ConditionItem) {
            throw new InvalidArgumentException('ConditionList element must be type ConditionItem.');
        }
        $this->conditions[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->conditions[$offset]);
    }

    public function add(ConditionItem $item)
    {
        $this->conditions[] = $item;
        return $this;
    }

    private function coarseModeFirst($operand)
    {
        if ($operand instanceof ComparableComponentInterface) {
            return $operand;
        }
        switch ($this->mode) {
            case ComparisonModes::COLUMN_COLUMN: case ComparisonModes::COLUMN_VALUE:
                return Query::column($operand);
            case ComparisonModes::VALUE_COLUMN: case ComparisonModes::VALUE_VALUE:
                return Query::value($operand);
        }
    }
    private function coarseModeSecond($operand)
    {
        if ($operand instanceof ComparableComponentInterface) {
            return $operand;
        }
        switch ($this->mode) {
            case ComparisonModes::COLUMN_COLUMN: case ComparisonModes::VALUE_COLUMN:
                return Query::column($operand);
            case ComparisonModes::COLUMN_VALUE: case ComparisonModes::VALUE_VALUE:
                return Query::value($operand);
        }
    }

    #region Relational operators
    private function genRelationalExpression($operand1, $operand2, $operator = RelationalOperators::EQUALS)
    {
        $operand1 = $this->coarseModeFirst($operand1);
        $operand2 = $this->coarseModeSecond($operand2);
        return new RelationalExpression($operand1, $operand2, $operator);
    }
    public function addRelational($operand1, $operand2, $operator = RelationalOperators::EQUALS, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genRelationalExpression($operand1, $operand2, $operator), $connector));
    }

    #region Relational operators with operator
    public function equals($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::EQUALS, $connector);
    }
    public function lessThan($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::LESS, $connector);
    }
    public function greaterThan($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::GREATER, $connector);
    }
    public function lessEquals($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::LESS_EQUALS, $connector);
    }
    public function greaterEquals($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::GREATER_EQUALS, $connector);
    }
    public function notEquals($operand1, $operand2, $connector = LogicConnectors::AND)
    {
        return $this->addRelational($operand1, $operand2, RelationalOperators::NOT_EQUALS, $connector);
    }
    #endregion

    #region Relational operators with connector
    public function andEquals($operand1, $operand2)
    {
        return $this->equals($operand1, $operand2, LogicConnectors::AND);
    }
    public function orEquals($operand1, $operand2)
    {
        return $this->equals($operand1, $operand2, LogicConnectors::OR);
    }
    public function andLessThan($operand1, $operand2)
    {
        return $this->lessThan($operand1, $operand2, LogicConnectors::AND);
    }
    public function orLessThan($operand1, $operand2)
    {
        return $this->lessThan($operand1, $operand2, LogicConnectors::OR);
    }
    public function andGreaterThan($operand1, $operand2)
    {
        return $this->greaterThan($operand1, $operand2, LogicConnectors::AND);
    }
    public function orGreaterThan($operand1, $operand2)
    {
        return $this->greaterThan($operand1, $operand2, LogicConnectors::OR);
    }
    public function andLessEquals($operand1, $operand2)
    {
        return $this->lessEquals($operand1, $operand2, LogicConnectors::AND);
    }
    public function orLessEquals($operand1, $operand2)
    {
        return $this->lessEquals($operand1, $operand2, LogicConnectors::OR);
    }
    public function andGreaterEquals($operand1, $operand2)
    {
        return $this->greaterEquals($operand1, $operand2, LogicConnectors::AND);
    }
    public function orGreaterEquals($operand1, $operand2)
    {
        return $this->greaterEquals($operand1, $operand2, LogicConnectors::OR);
    }
    public function andNotEquals($operand1, $operand2)
    {
        return $this->notEquals($operand1, $operand2, LogicConnectors::AND);
    }
    public function orNotEquals($operand1, $operand2)
    {
        return $this->notEquals($operand1, $operand2, LogicConnectors::OR);
    }
    #endregion
    
    #endregion

    #region LIKE
    private function genLikeExpression($operand1, $operand2, $negated = false)
    {
        $operand1 = $this->coarseModeFirst($operand1);
        $operand2 = $this->coarseModeSecond($operand2);
        return new LikeExpression($operand1, $operand2, $negated);
    }
    public function like($operand1, $operand2, $negated = false, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genLikeExpression($operand1, $operand2, $negated), $connector));
    }
    
    #region LIKE with connector and negation
    public function notLike($operand1, $operand2)
    {
        return $this->like($operand1, $operand2, true);
    }
    public function andLike($operand1, $operand2)
    {
        return $this->like($operand1, $operand2, false, LogicConnectors::AND);
    }
    public function orLike($operand1, $operand2)
    {
        return $this->like($operand1, $operand2, false, LogicConnectors::OR);
    }
    public function andNotLike($operand1, $operand2)
    {
        return $this->like($operand1, $operand2, true, LogicConnectors::AND);
    }
    public function orNotLike($operand1, $operand2)
    {
        return $this->like($operand1, $operand2, true, LogicConnectors::OR);
    }
    #endregion

    #endregion 

    #region REGEXP
    private function genRegexpExpression($value, $pattern, $negated = false)
    {
        $value = $this->coarseModeFirst($value);
        $pattern = $this->coarseModeSecond($pattern);
        return new RegexpExpression($value, $pattern, $negated);
    }
    public function regexp($value, $pattern, $negated = false, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genRegexpExpression($value, $pattern, $negated), $connector));
    }

    #region REGEXP with connector and negation
    public function notRegexp($value, $pattern)
    {
        return $this->regexp($value, $pattern, true);
    }
    public function andRegexp($value, $pattern)
    {
        return $this->regexp($value, $pattern, false, LogicConnectors::AND);
    }
    public function orRegexp($value, $pattern)
    {
        return $this->regexp($value, $pattern, false, LogicConnectors::OR);
    }
    public function andNotRegexp($value, $pattern)
    {
        return $this->regexp($value, $pattern, true, LogicConnectors::AND);
    }
    public function orNotRegexp($value, $pattern)
    {
        return $this->regexp($value, $pattern, true, LogicConnectors::OR);
    }
    #endregion

    #endregion

    #region NULL
    private function genNullExpression($value, $negated = false)
    {
        $value = $this->coarseModeFirst($value);
        return new NullExpression($value, $negated);
    }
    public function null($value, $negated = false, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genNullExpression($value, $negated), $connector));
    }

    #region NULL with connector and negation
    public function notNull($value)
    {
        return $this->null($value, true);
    }
    public function andNull($value)
    {
        return $this->null($value, false, LogicConnectors::AND);
    }
    public function orNull($value)
    {
        return $this->null($value, false, LogicConnectors::OR);
    }
    public function andNotNull($value)
    {
        return $this->null($value, true, LogicConnectors::AND);
    }
    public function orNotNull($value)
    {
        return $this->null($value, true, LogicConnectors::OR);
    }
    #endregion 

    #endregion

    #region Equals OR NULL
    public function equalsOrNull($expression, $value, $connector = LogicConnectors::AND)
    {
        $expression = $this->coarseModeFirst($expression);
        $value = $value instanceof SqlValue ? $value : Query::value($value);
        return $this->addExpression(function($conditions) use ($expression, $value) {
            $conditions->equals($expression, $value);
            $conditions->orNull($expression);
        }, false, $connector);
    }

    #region Equals OR NULL with connector
    public function andEqualsOrNull($expression, $value)
    {
        return $this->equalsOrNull($expression, $value, LogicConnectors::AND);
    }

    public function orEqualsOrNull($expression, $value)
    {
        return $this->equalsOrNull($expression, $value, LogicConnectors::OR);
    }
    #endregion

    #endregion

    #region BETWEEN
    private function genBetweenExpression($value, $minVal, $maxVal, $negated = false)
    {
        $value = $this->coarseModeFirst($value);
        $minVal = $this->coarseModeSecond($minVal);
        $maxVal = $this->coarseModeSecond($maxVal);
        return new BetweenExpression($value, $minVal, $maxVal, $negated);
    }
    public function between($value, $minVal, $maxVal, $negated = false, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genBetweenExpression($value, $minVal, $maxVal, $negated), $connector));
    }

    #region BETWEEN with connector and negation
    public function notBetween($value, $minVal, $maxVal)
    {
        return $this->between($value, $minVal, $maxVal, true);
    }
    public function andBetween($value, $minVal, $maxVal)
    {
        return $this->between($value, $minVal, $maxVal, false, LogicConnectors::AND);
    }
    public function orBetween($value, $minVal, $maxVal)
    {
        return $this->between($value, $minVal, $maxVal, false, LogicConnectors::OR);
    }
    public function andNotBetween($value, $minVal, $maxVal)
    {
        return $this->between($value, $minVal, $maxVal, true, LogicConnectors::AND);
    }
    public function orNotBetween($value, $minVal, $maxVal)
    {
        return $this->between($value, $minVal, $maxVal, true, LogicConnectors::OR);
    }
    #endregion
    
    #endregion

    #region IN
    private function genInExpression($operand, $values, $negated = false)
    {
        $operand = $this->coarseModeFirst($operand);
        $values = Query::array($values);
        return new InExpression($operand, $values, $negated);
    }
    public function in($operand, $values, $negated = false, $connector = LogicConnectors::AND)
    {
        return $this->add(new ConditionItem($this->genInExpression($operand, $values, $negated), $connector));
    }

    #region IN with connector and negation
    public function notIn($operand, $values)
    {
        return $this->in($operand, $values, true);
    }
    public function andIn($operand, $values)
    {
        return $this->in($operand, $values, false, LogicConnectors::AND);
    }
    public function orIn($operand, $values)
    {
        return $this->in($operand, $values, false, LogicConnectors::OR);
    }
    public function andNotIn($operand, $values)
    {
        return $this->in($operand, $values, true, LogicConnectors::AND);
    }
    public function orNotIn($operand, $values)
    {
        return $this->in($operand, $values, true, LogicConnectors::OR);
    }
    #endregion

    #endregion


    public function addExpression($expression, $negated = false, $connector = LogicConnectors::AND)
    {
        if (is_callable($expression)) {
            $expression = $this->getExpresionFromCallable($expression);
        }
        if (!$expression instanceof BooleanResultInterface) {
            throw new InvalidArgumentException('Invalid condition expression');
        }
        if ($negated && $expression instanceof NegatableInterface) {
            $expression->negate($negated);
        }
        return $this->add(new ConditionItem($expression, $connector));
    }

    private function getExpresionFromCallable(callable $callable)
    {
        $conditions = new self($this->mode);
        call_user_func($callable, $conditions);
        return $conditions;
    }

    public function and($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression, false, LogicConnectors::AND);
    }
    public function or($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression, false, LogicConnectors::OR);
    }
    public function andNot($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression, true, LogicConnectors::AND);
    }
    public function orNot($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression, true, LogicConnectors::OR);
    }

    public function __invoke($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression);
    }

    public function not($expression)
    {
        $expression = $this->multiArgsToExpression(func_get_args());
        return $this->addExpression($expression, true);
    }

    private function multiArgsToExpression($args)
    {
        if (empty($args)) return null;

        if (count($args) == 1) {
            return reset($args);
        }

        $first = current($args);
        $operator = next($args);
        $second = next($args);
        $third = next($args);

        if ($operator instanceof ComparableComponentInterface) {
            return $this->genRelationalExpression($first, $operator, RelationalOperators::EQUALS);
        }
        if (is_array($operator)) {
            return $this->genInExpression($first, $operator);
        }

        switch (strtoupper($operator)) {
            case RelationalOperators::EQUALS:
            case RelationalOperators::NOT_EQUALS:
            case RelationalOperators::LESS:
            case RelationalOperators::LESS_EQUALS:
            case RelationalOperators::GREATER:
            case RelationalOperators::GREATER_EQUALS:
                return $this->genRelationalExpression($first, $second, $operator);
            case 'LIKE':
                return $this->genLikeExpression($first, $second);
            case 'BETWEEN':
                return $this->genBetweenExpression($first, $second, $third);
            case 'NULL':
                return $this->genNullExpression($first);
            case 'NOT':
                if (is_array($second)) {
                    return $this->genInExpression($first, $second, true);
                }
                return $this->genRelationalExpression($first, $second, RelationalOperators::NOT_EQUALS);
            default:
                return $this->genRelationalExpression($first, $operator, RelationalOperators::EQUALS);
        }
    }
}