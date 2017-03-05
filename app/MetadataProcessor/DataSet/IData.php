<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/19/17
 * Time: 8:25 PM
 */

namespace Bookmarker\MetadataProcessor\DataSet;

/**
 * Interface IData
 * @package Bookmarker\MetadataProcessor\DataSet
 */
interface IData
{

    public function getGenre();

    public function getTitle();

    public function getYear();

    public function getLang();

    public function getAuthors();

    public function getCover();

    public function getDescription();

    public function toArray();

}