<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 10:07 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Book
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\BookRepository")
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="Book",
 *   type="object"
 * )
 */
class Book
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
     * @ORM\Column(type="string", length=4, nullable=false, options={"fixed": true})
     * @SWG\Property(type="string")
     */
    private $ext;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, name="file_path")
     * @SWG\Property(type="string")
     */
    private $filePath;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $title = 'Unknown';

    /**
     * @var int
     * @ORM\Column(type="integer", length=4, nullable=true)
     * @JMS\Expose
     * @SWG\Property(type="integer")
     */
    private $year;

    /**
     * @var string
     * @ORM\Column (type="string", length=3, nullable=true, options={"fixed": true})
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    private $lang;

    /**
     * @var Author[]
     * @ORM\ManyToMany(targetEntity="Author", mappedBy="books")
     * @SWG\Property(type="string")
     * @JMS\Expose
     */
    private $authors;

    /**
     * @var Genre
     * @ORM\ManyToOne(targetEntity="Genre", inversedBy="books")
     * @ORM\JoinColumn(name="genre_id", onDelete="SET NULL")
     * @SWG\Property(type="string")
     * @JMS\Expose
     */
    private $genre;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property()
     */
    private $mime;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="books")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     * @JMS\Expose
     * @SWG\Property(
     *     @SWG\Schema(ref="#/definitions/User")
     * )
     */
    private $user;

    /**
     * @var BookCovers[]
     * @ORM\OneToMany(targetEntity="BookCovers", mappedBy="book")
     * @SWG\Property()
     */
    private $bookCovers;

    /**
     * @var string
     * @ORM\Column(type="text", length=1000)
     * @JMS\Expose
     * @SWG\Property()
     */
    private $description = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bookCovers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ext
     *
     * @param string $ext
     *
     * @return Book
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get ext
     *
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Set filePath
     *
     * @param string $filePath
     *
     * @return Book
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get filePath
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Book
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return Book
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Add author
     *
     * @param \Bookmarker\Db\Entities\Author $author
     *
     * @return Book
     */
    public function addAuthor(\Bookmarker\Db\Entities\Author $author)
    {
        $this->authors[] = $author;
        $author->addBook($this);
        return $this;
    }

    /**
     * Remove author
     *
     * @param \Bookmarker\Db\Entities\Author $author
     */
    public function removeAuthor(\Bookmarker\Db\Entities\Author $author)
    {
        $this->authors->removeElement($author);
        $author->removeBook($this);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set genre
     *
     * @param \Bookmarker\Db\Entities\Genre $genre
     *
     * @return Book
     */
    public function setGenre(\Bookmarker\Db\Entities\Genre $genre = null)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return \Bookmarker\Db\Entities\Genre
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     * @return $this
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("book_link")
     * @return string
     */
    public function getDownloadLink()
    {
        $metaName = pathinfo($this->getFilePath(), PATHINFO_BASENAME);

        return urlencode("/book/$metaName");
    }

    /**
     * Set User
     *
     * @param \Bookmarker\Db\Entities\User $user
     *
     * @return User
     */
    public function setUser(\Bookmarker\Db\Entities\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get genre
     *
     * @return \Bookmarker\Db\Entities\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add bookCover
     *
     * @param \Bookmarker\Db\Entities\BookCovers $bookCover
     *
     * @return Book
     */
    public function addBookCover(\Bookmarker\Db\Entities\BookCovers $bookCover)
    {
        $this->bookCovers[] = $bookCover;

        return $this;
    }

    /**
     * Remove bookCover
     *
     * @param \Bookmarker\Db\Entities\BookCovers $bookCover
     */
    public function removeBookCover(\Bookmarker\Db\Entities\BookCovers $bookCover)
    {
        $this->bookCovers->removeElement($bookCover);
    }

    /**
     * Get bookCovers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBookCovers()
    {
        return $this->bookCovers;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this;
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }



    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("cover")
     * @return string
     */
    public function getMainCover()
    {
        $main = $this->getBookCovers()->filter(function(BookCovers $cover) {
            return $cover->getIsMain();
        });

        if (!$main->isEmpty()) {
            return $main->first()->getDownloadLink();
        }
    }
}
