<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/26/17
 * Time: 6:49 PM
 */

namespace Bookmarker\Db\Repositories;

use Bookmarker\Db\Entities\Book;
use Bookmarker\Db\Entities\Votes;
use Bookmarker\Registry;

/**
 * Class VotesRepository
 * @package Bookmarker\Db\Repositories
 */
class VotesRepository extends Repository
{

    const VOTE_CREATED = 1;

    const VOTE_CHANGED = 2;

    /**
     * @param Book $book
     * @param array $params
     * @return int
     */
    public function voteForBook(Book $book, array $params)
    {
        $result = self::VOTE_CHANGED;
        $em = $this->getEntityManager();
        if (!isset($params['vote'])) {
            throw new \InvalidArgumentException('some author_ids are not exist');
        }
        $voteValue = abs((int)$params['vote']);
        $voteValue = $voteValue === 0 ? Votes::MIN_VOTE_VALUE : $voteValue;
        $voteValue = $voteValue > Votes::MAX_VOTE_VALUE ? Votes::MAX_VOTE_VALUE : $voteValue;
        $app = Registry::get('app');
        $user = $app['security.token_storage']->getToken()->getUser();
        $voteEntity = $this->findOneBy(array(
            'users' => $user,
            'books' => $book
        ));
        if (is_null($voteEntity)) {
            $voteEntity = new Votes();
            $voteEntity->setBooks($book)
                ->setUsers($user);
            $result = self::VOTE_CREATED;
        }
        $voteEntity->setVote($voteValue);
        $em->persist($voteEntity);
        $em->flush();
        return $result;
    }

    /**
     * @param Book $book
     * @return float
     */
    public function getRating(Book $book)
    {
        $dql = 'SELECT AVG(v.vote) FROM \Bookmarker\Db\Entities\Votes v WHERE v.books = :book GROUP BY v.books';
        return $this->getEntityManager()->createQuery($dql)
            ->setParameter('book', $book)
            ->getSingleScalarResult();
    }

    /**
     * @param Book $book
     */
    public function deleteVote(Book $book)
    {
        $app = Registry::get('app');
        $user = $app['security.token_storage']->getToken()->getUser();
        $voteEntity = $this->findOneBy(array(
            'users' => $user,
            'books' => $book
        ));
        if ($voteEntity instanceof Votes) {
            $this->getEntityManager()->remove($voteEntity);
            $this->getEntityManager()->flush();
        }
    }
}