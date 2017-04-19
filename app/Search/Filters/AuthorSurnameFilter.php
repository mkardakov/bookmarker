<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/19/17
 * Time: 9:47 PM
 */

namespace Bookmarker\Search\Filters;


use Bookmarker\Search\Filterable;
use Doctrine\ORM\Query\Expr;

/**
 * Class AuthorSurnameFilter
 * @package Bookmarker\Search\Filters
 */
class AuthorSurnameFilter extends Filterable
{

    /**
     * @param string $rawValue
     * @return Expr\Comparison
     */
    public function getExpression($rawValue = '')
    {
        $expression = new Expr();
        $rawValue = "$rawValue%";
        return (new Expr())->like('a.surname', $expression->literal($rawValue));
    }

    public function getAlias()
    {
        return null;
    }

    public function isLiteral()
    {
        return true;
    }
}