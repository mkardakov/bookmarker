<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 6:06 PM
 */

namespace Bookmarker\MetadataProcessor;

use Bookmarker\FileDrivers\IDriver;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Metadata
 * @package Bookmarker\MetadataProcessor
 */
abstract class Metadata
{
    /**
     * @var
     */
    protected $metadata;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile;

    /**
     * @var IDriver
     */
    protected $fileDriver;

    /**
     * @var string
     */
    protected $filePath = '';

    /**
     * @var mixed
     */
    protected $originalFile;

    abstract protected function getMetaData();

    abstract public static function getMimeType();

    /**
     * Book constructor.
     * @param UploadedFile $file
     */
    public function __construct(UploadedFile $file)
    {
        $this->uploadedFile = $file;
    }

    /**
     * @return mixed
     */
    public function getOriginalFile()
    {
        return $this->originalFile;
    }

    /**
     * @return bool
     */
    public function storeFile()
    {
        return $this->fileDriver->store($this->uploadedFile);
    }

    /**
     * @return IDriver
     */
    public function getFileDriver()
    {
        return $this->fileDriver;
    }

    /**
     * @param IDriver $fileDriver
     * @return $this
     */
    public function setFileDriver(IDriver $fileDriver)
    {
        $this->fileDriver = $fileDriver;

        return $this;
    }

}