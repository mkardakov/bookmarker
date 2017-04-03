<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/12/17
 * Time: 6:27 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class BookCovers
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\BookRepository")
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="BookCover",
 *   type="object"
 * )
 */
class BookCovers
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
     * @ORM\Column(type="string", nullable=false)
     * @SWG\Property(type="string")
     */
    private $imagePath;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, name="is_main")
     * @JMS\Expose
     * @SWG\Property(type="boolean")
     */
    private $isMain = 0;

    /**
     * @var Book
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="bookCovers")
     * @ORM\JoinColumn(name="book_id", onDelete="CASCADE")
     */
    private $book;


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
     * Set imagePath
     *
     * @param string $imagePath
     *
     * @return BookCovers
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set isMain
     *
     * @param boolean $isMain
     *
     * @return BookCovers
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * Get isMain
     *
     * @return boolean
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * Set book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     *
     * @return BookCovers
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
     * @JMS\VirtualProperty
     * @JMS\SerializedName("book_cover_link")
     * @return string
     */
    public function getDownloadLink()
    {
        $metaName = pathinfo($this->getImagePath(), PATHINFO_BASENAME);

        return urlencode("/book/{$this->getBook()->getId()}/covers/$metaName");
    }
}