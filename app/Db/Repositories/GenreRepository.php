<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/5/17
 * Time: 7:59 PM
 */

namespace Bookmarker\Db\Repositories;
use Bookmarker\Db\Entities\Genre;

/**
 * Class GenreRepository
 * @package Bookmarker\Db\Repositories
 */
class GenreRepository extends Repository
{

    /**
     * @param array $params
     * @return int
     */
    public function addGenre(array $params)
    {
        $genreEntity = new Genre();
        $em = $this->getEntityManager();
        if (array_key_exists('title', $params)) {
            $genreEntity->setTitle($params['title']);
        }
        $em->persist($genreEntity);
        $em->flush();
        return $genreEntity->getId();
    }

    /**
     * @param Genre $genreEntity
     * @param array $params
     */
    public function updateGenre(Genre $genreEntity, array $params)
    {
        $em = $this->getEntityManager();
        if (array_key_exists('title', $params)) {
            $genreEntity->setTitle($params['title']);
        }
        $em->persist($genreEntity);
        $em->flush();
    }

    /**
     * @param Genre $genreEntity
     */
    public function deleteGenre(Genre $genreEntity)
    {
        $em = $this->getEntityManager();
        $em->remove($genreEntity);
        $em->flush();
    }
}