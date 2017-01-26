<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 10:28 PM
 */

namespace Bookmarker\Db\Entities;

use  \Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Genre
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\GenreRepository")
 * @ORM\Table(name="Genre",uniqueConstraints={@ORM\UniqueConstraint(name="title_idx", columns={"title"})})
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="Genre",
 *   type="object"
 * )
 */
class Genre
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue
     * @JMS\Expose
     * @SWG\Property(type="integer")
     */
    private $id;

    /**
     * @var Book[]
     * @ORM\OneToMany(targetEntity="Book", mappedBy="genre")
     */
    private $books;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $title;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBooks()
    {
        return $this->books;
    }

    public function setBook(Book $book)
    {
        $this->books[] = $book;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $id
     * @return $this;
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $title
     * @return $this;
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


}