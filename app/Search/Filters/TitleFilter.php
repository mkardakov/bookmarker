<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 8:11 PM
 */

namespace Bookmarker\Search\Filters;


use Bookmarker\Search\Filterable;
use Doctrine\ORM\Query\Expr;

/**
 * Class TitleFilter
 * @package Bookmarker\Search\Filters
 */
class TitleFilter extends Filterable
{

    /**
     * @param string $rawValue
     * @return Expr\Comparison
     */
    public function getExpression($rawValue = '')
    {
        $expression = new Expr();
        $rawValue = "%$rawValue%";
        return $expression->like('b.title', $expression->literal($rawValue));
    }

    /**
     * @return null
     */
    public function getAlias()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isLiteral()
    {
        return true;
    }
}