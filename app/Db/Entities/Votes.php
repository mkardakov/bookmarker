<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/26/17
 * Time: 6:48 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Votes
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\VotesRepository")
 * @ORM\Table(name="Votes",uniqueConstraints={@ORM\UniqueConstraint(name="book_user_ux", columns={"books_id","users_id"})})
 */
class Votes
{
    const MAX_VOTE_VALUE = 5;

    const MIN_VOTE_VALUE = 1;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    private $id;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="users_id", onDelete="SET NULL")
     */
    private $users;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="Book")
     * @ORM\JoinColumn(name="books_id", onDelete="CASCADE")
     */
    private $books;

    /**
     * @var integer
     * @ORM\Column(type="smallint", options={"unsigned":true})
     */
    private $vote;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->books = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set vote
     *
     * @param integer $vote
     *
     * @return Votes
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote
     *
     * @return integer
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Set users
     *
     * @param \Bookmarker\Db\Entities\User $users
     *
     * @return Votes
     */
    public function setUsers(\Bookmarker\Db\Entities\User $users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get users
     *
     * @return \Bookmarker\Db\Entities\User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set books
     *
     * @param \Bookmarker\Db\Entities\Book $books
     *
     * @return Votes
     */
    public function setBooks(\Bookmarker\Db\Entities\Book $books)
    {
        $this->books = $books;

        return $this;
    }

    /**
     * Get books
     *
     * @return \Bookmarker\Db\Entities\Book[]
     */
    public function getBooks()
    {
        return $this->books;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}