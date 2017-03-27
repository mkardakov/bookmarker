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
use Doctrine\ORM\AbstractQuery;

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
            throw new \InvalidArgumentException('no vote value received');
        }
        $voteValue = (int)$params['vote'];
        if ($voteValue < Votes::MIN_VOTE_VALUE || $voteValue > Votes::MAX_VOTE_VALUE) {
            throw new \InvalidArgumentException(sprintf(
                'vote value must be in %d..%d range',
                Votes::MIN_VOTE_VALUE,
                Votes::MAX_VOTE_VALUE
            ));
        }
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
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
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