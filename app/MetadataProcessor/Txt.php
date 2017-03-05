<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:34 PM
 */

namespace Bookmarker\MetadataProcessor;
use Bookmarker\MetadataProcessor\DataSet\Storage;

/**
 * Class Txt
 * @package Bookmarker\MetadataProcessor
 */
class Txt extends Metadata
{

    /**
     * @return string
     */
    public static function getMimeType()
    {
        return ['text/plain'];
    }

    /**
     * @param $filePath
     * @return Storage
     */
    protected function processInfo($filePath)
    {
        $this->info = new Storage();
    }
}