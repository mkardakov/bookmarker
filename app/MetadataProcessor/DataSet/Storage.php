<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/19/17
 * Time: 12:55 PM
 */

namespace Bookmarker\MetadataProcessor\DataSet;

/**
 * Class Storage
 * @package Bookmarker\MetadataProcessor
 */
class Storage implements IData
{
    const DEFAULT_ENCODING = 'UTF-8';

    protected $genre;

    protected $title;

    protected $year;

    protected $lang;

    protected $authors = [];

    protected $cover;

    protected $description = '';

    /**
     * @param mixed $title
     * @return $this;
     */
    public function setTitle($title)
    {
        $this->title = mb_convert_encoding($title, self::DEFAULT_ENCODING);
        return $this;
    }

    /**
     * @param mixed $year
     * @return $this;
     */
    public function setYear($year)
    {
        $this->year = (int)$year;
        return $this;
    }

    /**
     * @param mixed $lang
     * @return $this;
     */
    public function setLang($lang)
    {
        $this->lang = mb_convert_encoding($lang, self::DEFAULT_ENCODING);
        return $this;
    }

    /**
     * @param string $author
     * @return $this;
     */
    public function setAuthor($author)
    {
        $this->authors[] = mb_convert_encoding($author, self::DEFAULT_ENCODING);
        return $this;
    }

    /**
     * @param mixed $cover
     * @return $this;
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
        return $this;
    }

    /**
     * @param mixed $genre
     * @return $this;
     */
    public function setGenre($genre)
    {
        $this->genre = mb_convert_encoding($genre, self::DEFAULT_ENCODING);
        return $this;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getCover()
    {
        return $this->cover;
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
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->getTitle(),
            'year'  => $this->getYear(),
            'lang'  => $this->getLang(),
            'authors' => $this->getAuthors(),
            'genre' => $this->getGenre(),
            'cover' => $this->getCover(),
            'description' => $this->getDescription()
        ];
    }
}