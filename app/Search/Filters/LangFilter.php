<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 11:01 PM
 */

namespace Bookmarker\Search\Filters;

use Bookmarker\Search\Filterable;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\Query\Expr;

/**
 * Class LangFilter
 * @package Bookmarker\Search\Filters
 */
class LangFilter extends Filterable
{


    /**
     * @return ExpressionBuilder
     */
    public function getExpression()
    {
        return (new Expr())->eq('b.lang', $this->getAlias());
    }

    public function getAlias()
    {
        return ':lang';
    }

    public function isLiteral()
    {
        return false;
    }
}