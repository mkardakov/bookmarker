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
class Mobi extends Metadata
{
    use ExiftoolHelper;

    const MIME = 'application/x-mobipocket-ebook';

    /**
     * @var Storage
     */
    protected $info;

    /**
     * @return string
     */
    public static function getMimeType()
    {
        return self::MIME;
    }

    /**
     * @param $filePath
     */
    protected function processInfo($filePath)
    {
        $this->info = $this->parse($filePath);
    }

}