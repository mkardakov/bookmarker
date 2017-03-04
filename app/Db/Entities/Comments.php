<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 10:28 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Comments
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\CommentsRepository")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="Comments",
 *   type="object"
 * )
 */
class Comments
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @JMS\Expose
     * @SWG\Property(type="integer")
     */
    private $id;

    /**
     * @var User[]
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     * @JMS\Expose
     * @SWG\Property(
     *     @SWG\Schema(ref="#/definitions/User")
     * )
     */
    private $commentator;

    /**
     * @var Book[]
     * @ORM\ManyToOne(targetEntity="Book")
     * @ORM\JoinColumn(name="book_id", onDelete="CASCADE")
     */
    private $book;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $text;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @JMS\Expose
     * @SWG\Property(type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Expose
     * @SWG\Property(type="datetime")
     */
    private $modificationDate;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Comments
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Comments
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     *
     * @return Comments
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set commentator
     *
     * @param \Bookmarker\Db\Entities\User $commentator
     *
     * @return Comments
     */
    public function setCommentator(\Bookmarker\Db\Entities\User $commentator = null)
    {
        $this->commentator = $commentator;

        return $this;
    }

    /**
     * Get commentator
     *
     * @return \Bookmarker\Db\Entities\User
     */
    public function getCommentator()
    {
        return $this->commentator;
    }

    /**
     * Set book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     *
     * @return Comments
     */
    public function setBook(\Bookmarker\Db\Entities\Book $book = null)
    {
        $this->book = $book;

        return $this;
    }

    /**
     * Get book
     *
     * @return \Bookmarker\Db\Entities\Book
     */
    public function getBook()
    {
        return $this->book;
    }


    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->creationDate = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update

     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->modificationDate = new \DateTime("now");
    }
}