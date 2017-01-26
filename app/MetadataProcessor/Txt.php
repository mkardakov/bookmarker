<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:34 PM
 */

namespace Bookmarker\MetadataProcessor;

/**
 * Class Txt
 * @package Bookmarker\MetadataProcessor
 */
class Txt extends Metadata
{

    const MIME = 'text/plain';

    protected function getMetaData()
    {
        return [];
    }

    public static function getMimeType()
    {
        return self::MIME;
    }
}