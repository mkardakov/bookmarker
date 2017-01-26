<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:34 PM
 */

namespace Bookmarker\MetadataProcessor;

/**
 * Class Pdf
 * @package Bookmarker\MetadataProcessor
 */
class Pdf extends Metadata
{

    const MIME = 'application/pdf';

    protected function getMetaData()
    {
        return [];
    }

    public static function getMimeType()
    {
        return self::MIME;
    }
}