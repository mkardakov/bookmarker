<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 11:12 PM
 */

namespace Bookmarker\Db\Repositories;

use Bookmarker\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * Class Repository
 * @package Bookmarker\Db\Repository
 */
abstract class Repository extends EntityRepository
{

    /**
     * @var array
     */
    protected $searchCriteria = [];

    /**
     * @return array
     */
    public function getFields()
    {
        $properties = $this->getClassMetadata()->getFieldNames();
        return array_merge(
            $properties,
            $this->getClassMetadata()->getAssociationNames()
        );
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $order
     * @return mixed
     */
    public function findLimited($page = 1, $limit = 0, $order = [])
    {
        list($order, $limit, $offset) = call_user_func_array([$this, 'validateLimitedParams'], func_get_args());
        return $this->findBy($this->searchCriteria, $order, $limit, $offset);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $order
     * @return Criteria
     */
    public function buildLimitedCriteria($page = 1, $limit = 0, $order = [])
    {
        list($order, $limit, $offset) = call_user_func_array([$this, 'validateLimitedParams'], func_get_args());
        $criteria = Criteria::create()
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        if (!empty($order)) {
            $criteria->orderBy($order);
        }
        return $criteria;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $order
     * @return array
     */
    protected function validateLimitedParams($page = 1, $limit = 0, $order = [])
    {
        if ($limit <= 0) {
            $limit = $this->getDefaultLimit();
        }
        if ($limit > $this->getMaxLimit()) {
            $limit = $this->getMaxLimit();
        }
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $limit;
        return [$order, $limit, $offset];
    }


    /**
     * @return int
     */
    protected function getDefaultLimit()
    {
        return Registry::get('app')['config'][APP_ENV]['default_record_number'];
    }

    /**
     * @return int
     */
    protected function getMaxLimit()
    {
        return Registry::get('app')['config'][APP_ENV]['max_record_number'];
    }

}