<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/17/17
 * Time: 1:48 PM
 */

namespace Bookmarker\Search;

use Doctrine\ORM\QueryBuilder;

/**
 * Class CriteriaFactory
 * @package Bookmarker\Search
 */
class SearchQueryBuilder
{

    /**
     * @var array
     */
    protected static $map = array(
        'title' => '\Bookmarker\Search\Filters\TitleFilter',
        'author_id' => '\Bookmarker\Search\Filters\AuthorFilter',
        'author_surname' => '\Bookmarker\Search\Filters\AuthorSurnameFilter',
        'genre_id' => '\Bookmarker\Search\Filters\GenreFilter',
        'lang' => '\Bookmarker\Search\Filters\LangFilter'
    );

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var array
     */
    protected $fieldsToSearch = array();

    /**
     * SearchQueryBuilder constructor.
     * @param array $fieldsToSearch
     */
    public function __construct(array $fieldsToSearch)
    {
        $this->fieldsToSearch = $fieldsToSearch;
    }

    /**
     * @param QueryBuilder $qb
     * @return $this
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function build()
    {
        $collection = array();
        $resultExpr = false;
        $toProcess = array_intersect_key(self::$map, $this->fieldsToSearch);
        if (!empty($toProcess)) {
            foreach ($toProcess as $key => $className) {
                if (!class_exists($className)) {
                    throw new \Exception(sprintf('Unknown class called %s', $className));
                }
                $expr = new $className();
                if ($expr->isLiteral()) {
                    $collection[] = $expr->getExpression($this->fieldsToSearch[$key]);
                } else {
                    $collection[] = $expr->getExpression();
                    $this->qb->setParameter($expr->getAlias(), $this->fieldsToSearch[$key]);
                }
            }
            $resultExpr = call_user_func_array(array($this->qb->expr(), 'andX'), $collection);
        }
        return $resultExpr;
    }

}