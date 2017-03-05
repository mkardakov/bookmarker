<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/27/17
 * Time: 10:04 PM
 */

namespace Bookmarker\Db\Repositories;

use Bookmarker\Db\Entities\Book;
use Bookmarker\Db\Entities\Comments;
use Bookmarker\Registry;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

/**
 * Class CommentsRepository
 * @package Bookmarker\Db\Repositories
 */
class CommentsRepository extends Repository
{

    /**
     * @param Book $book
     * @param array $data
     * @return Comments
     */
    public function addComment(Book $book, array $data)
    {
        if (!array_key_exists('text', $data)) {
            throw new InvalidArgumentException('text property not found');
        }
        $em = $this->getEntityManager();
        $app = Registry::get('app');
        $user =  $app['security.token_storage']->getToken()->getUser();
        $comment = new Comments();

        $comment->setBook($book)
            ->setCommentator($user)
            ->setText($data['text']);
        $em->persist($comment);
        $em->flush();
        return $comment;
    }

    /**
     * @param Comments $comment
     * @param array $data
     * @return Comments
     */
    public function updateComment(Comments $comment, array $data)
    {
        if (!array_key_exists('text', $data)) {
            throw new InvalidArgumentException('text property not found');
        }
        $em = $this->getEntityManager();
        $app = Registry::get('app');
        $user =  $app['security.token_storage']->getToken()->getUser();

        $comment->setText($data['text']);
        $em->persist($comment);
        $em->flush();
        return $comment;
    }

    /**
     * @param Comments $comment
     */
    public function deleteComment(Comments $comment)
    {
        $em = $this->getEntityManager();
        $em->remove($comment);
        $em->flush();
    }
}