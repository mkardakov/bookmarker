<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:36 PM
 */

namespace Bookmarker\MetadataProcessor;

use Bookmarker\FileDrivers\LocalDriver;
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
        '\Bookmarker\MetadataProcessor\Pdf',
        '\Bookmarker\MetadataProcessor\Txt',
        '\Bookmarker\MetadataProcessor\Mobi',
        '\Bookmarker\MetadataProcessor\Epub',
    );

    /**
     * @param UploadedFile $file
     * @return mixed
     * @throws \ErrorException
     */
    public function __invoke(UploadedFile $file)
    {
        $driver = new LocalDriver($file, true);
        $mime = $driver->getMimeType();
        if ($class = $this->fetchClassByMime($mime)) {
            return new $class($file);
        }
        throw new \ErrorException('Invalid mime type of the file');
    }

    /**
     * Gets handler class by Mime type
     * @param string $mime
     * @return bool|string
     * @throws \ErrorException
     */
    protected function fetchClassByMime($mime)
    {
        foreach (self::$map as $metaClass) {
            if (!class_exists($metaClass)) {
                throw new \ErrorException(sprintf(
                    'Invalid configuration of metadata map. Class %s not exist',
                    $metaClass
                ));
            }
            $mimeTypes = $metaClass::getMimeType();
            if (in_array($mime, $mimeTypes)) {
                return $metaClass;
            }
        }
        return false;
    }
}