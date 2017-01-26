<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 11:12 PM
 */

namespace Bookmarker\Db\Repositories;
use Doctrine\ORM\EntityRepository;

/**
 * Class Repository
 * @package Bookmarker\Db\Repository
 */
abstract class Repository extends EntityRepository
{

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
}