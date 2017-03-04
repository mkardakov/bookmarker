<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/23/17
 * Time: 11:11 PM
 */

namespace Bookmarker\MetadataProcessor;

use Bookmarker\MetadataProcessor\DataSet\Storage;
use Bookmarker\MetadataProcessor\Exiftool\ExiftoolHelper;
/**
 * Class Mobi
 * @package Bookmarker\MetadataProcessor
 */
class Epub extends Metadata
{
    use ExiftoolHelper;

    /**
     * @var Storage
     */
    protected $info;

    /**
     * @return string
     */
    public static function getMimeType()
    {
        return ['application/zip', 'application/epub+zip'];
    }

    /**
     * @param $filePath
     */
    protected function processInfo($filePath)
    {
        $this->info = $this->parse($filePath);
    }

}