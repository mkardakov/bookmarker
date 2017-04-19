<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 1:51 PM
 */

namespace Bookmarker\Search;

use Doctrine\Common\Collections\ExpressionBuilder;

/**
 * Interface Filterable
 * @package Bookmarker\Search
 */
abstract class Filterable
{
    /**
     * @var mixed
     */
    protected $val;

    /**
     * @return ExpressionBuilder
     */
    abstract public function getExpression();

    abstract public function getAlias();

    abstract public function isLiteral();

    /**
     * @param $value
     * @return $this
     */
    public function acceptVal($value)
    {
        $this->val = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVal()
    {
        return $this->val;
    }

}