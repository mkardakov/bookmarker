<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/7/17
 * Time: 10:31 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Book
 * @package Bookmarker\Db\Entities
 * @ORM\Entity
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="File",
 *   type="object"
 * )
 */
class File
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
     */
    private $filePath;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property()
     */
    private $mime;

    /**
     * @var Book
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="files"))
     * @ORM\JoinColumn(name="book_id", onDelete="CASCADE", nullable=false)
     *
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
     * Set ext
     *
     * @param string $ext
     *
     * @return File
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
     * @return File
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
     * Set mime
     *
     * @param string $mime
     *
     * @return File
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Get mime
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     *
     * @return File
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
     * @JMS\SerializedName("book_link")
     * @return string
     */
    public function getDownloadLink()
    {
        $metaName = pathinfo($this->getFilePath(), PATHINFO_BASENAME);

        return urlencode("/book/$metaName");
    }

}