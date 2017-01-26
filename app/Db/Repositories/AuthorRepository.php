<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/5/17
 * Time: 7:59 PM
 */

namespace Bookmarker\Db\Repositories;
use Bookmarker\Db\Entities\Author;

/**
 * Class AuthorRepository
 * @package Bookmarker\Db\Repositories
 */
class AuthorRepository extends Repository
{

    /**
     * @param array $params
     * @return int
     */
    public function addAuthor(array $params)
    {
        $authorEntity = new Author();
        $em = $this->getEntityManager();
        if (array_key_exists('name', $params)) {
            $authorEntity->setName($params['name']);
        }
        if (array_key_exists('surname', $params)) {
            $authorEntity->setSurname($params['surname']);
        }
        $em->persist($authorEntity);
        $em->flush();
        return $authorEntity->getId();
    }

    /**
     * @param Author $authorEntity
     * @param array $params
     */
    public function updateAuthor(Author $authorEntity, array $params)
    {
        $em = $this->getEntityManager();
        if (array_key_exists('name', $params)) {
            $authorEntity->setName($params['name']);
        }
        if (array_key_exists('surname', $params)) {
            $authorEntity->setSurname($params['surname']);
        }
        $em->persist($authorEntity);
        $em->flush();
    }

    /**
     * @param Author $authorEntity
     */
    public function deleteAuthor(Author $authorEntity)
    {
        $em = $this->getEntityManager();
        $em->remove($authorEntity);
        $em->flush();
    }
}