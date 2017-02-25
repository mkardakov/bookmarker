<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:34 PM
 */

namespace Bookmarker\MetadataProcessor;

use Bookmarker\MetadataProcessor\DataSet\Storage;
use Bookmarker\MetadataProcessor\Exiftool\ExiftoolHelper;

/**
 * Class Pdf
 * @package Bookmarker\MetadataProcessor
 */
class Pdf extends Metadata
{
    use ExiftoolHelper;

    const MIME = 'application/pdf';

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