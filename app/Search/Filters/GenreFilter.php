<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 8:33 PM
 */

namespace Bookmarker\Search\Filters;

use Bookmarker\Search\Filterable;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\Query\Expr;

/**
 * Class GenreFilter
 * @package Bookmarker\Search\Filters
 */
class GenreFilter extends Filterable
{

    /**
     * @return ExpressionBuilder
     */
    public function getExpression()
    {
        $expression = new Expr();
        return $expression->eq('b.genre', $this->getAlias());
    }

    public function getAlias()
    {
        return ":genre_id";
    }

    public function isLiteral()
    {
        return false;
    }
}