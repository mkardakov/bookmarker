<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 10:41 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Authors
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\AuthorRepository")
 * @ORM\Table(name="Author",uniqueConstraints={@ORM\UniqueConstraint(name="fullName_idx", columns={"name", "surname"})})
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="Author",
 *   type="object"
 * )
 */
class Author
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @JMS\Expose
     * @SWG\Property(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $surname;

    /**
     * @var Book[]
     * @ORM\ManyToMany(targetEntity="Book", inversedBy="authors")
     * @ORM\JoinTable(name="books_authors")
     */
    private $books;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->books = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Author
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return Author
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Add book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     *
     * @return Author
     */
    public function addBook(\Bookmarker\Db\Entities\Book $book)
    {
        $this->books[] = $book;

        return $this;
    }

    /**
     * Remove book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     */
    public function removeBook(\Bookmarker\Db\Entities\Book $book)
    {
        $this->books->removeElement($book);
    }

    /**
     * Get books
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBooks()
    {
        return $this->books;
    }
}