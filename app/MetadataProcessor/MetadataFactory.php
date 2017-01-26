<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:36 PM
 */

namespace Bookmarker\MetadataProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MetadataFactory
 * @package Bookmarker\MetadataProcessor
 */
class MetadataFactory
{

    /**
     * @var array
     */
    protected static $map = array(
        Pdf::MIME => '\Bookmarker\MetadataProcessor\Pdf',
        Txt::MIME => '\Bookmarker\MetadataProcessor\Txt'
    );

    /**
     * @param UploadedFile $file
     * @return mixed
     * @throws \ErrorException
     */
    public function __invoke(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        if (!isset(self::$map[$mime])) {
            throw new \ErrorException('Invalid mime type of the file');
        }
        return new self::$map[$mime]($file);
    }
}