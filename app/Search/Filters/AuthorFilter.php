<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 10:55 PM
 */

namespace Bookmarker\Search\Filters;

use Bookmarker\Search\Filterable;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\Query\Expr;

/**
 * Class AuthorFilter
 * @package Bookmarker\Search\Filters
 */
class AuthorFilter extends Filterable
{

    /**
     * @return ExpressionBuilder
     */
    public function getExpression()
    {
        return (new Expr())->eq('a.id', $this->getAlias());
    }

    public function getAlias()
    {
        return ':author';
    }

    public function isLiteral()
    {
        return false;
    }
}