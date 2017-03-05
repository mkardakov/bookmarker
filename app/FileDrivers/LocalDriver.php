<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:27 PM
 */

namespace Bookmarker\FileDrivers;


use Bookmarker\MetadataProcessor\Exiftool\BookReader;
use Bookmarker\Registry;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Monolog\Logger;

/**
 * Class LocalDriver
 * @package Bookmarker\FileDrivers
 */
class LocalDriver implements IDriver
{

    /**
     * @var File
     */
    protected $file;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var string
     */
    protected $ext;

    /**
     * LocalDriver constructor.
     * @param $fileName
     * @param bool $isAbsolutePath
     */
    public function __construct($fileName, $isAbsolutePath = false)
    {
        $app = Registry::get('app');
        $fullFileName = $fileName;
        if (!$isAbsolutePath) {
            $fullFileName = ROOT . DIRECTORY_SEPARATOR .
                $app['config'][APP_ENV]['file_storage'][APP_FILE_STORAGE]['path'] .
                DIRECTORY_SEPARATOR . $fileName;
        }
        $this->file = new File($fullFileName);
        $this->loadMetaInformation();
    }

    /**
     * @return bool
     * @throws \ErrorException
     */
    public function store()
    {
        $app = Registry::get('app');
        $path = ROOT . DIRECTORY_SEPARATOR . $app['config'][APP_ENV]['file_storage'][APP_FILE_STORAGE]['path'];
        if (!is_dir($path)) {
            mkdir($path, 0757, true);
        }
        $this->file = $this->file->move($path, md5(time()) . '.' . $this->getExt());
        return $this->file->isFile();
    }

    /**
     * @return resource
     */
    public function getDownloadLink()
    {
        $path = $this->file->getRealPath();
        if (!is_file($path)) {
            throw new NotFoundHttpException('File not found', 404);
        }
        return fopen($path, 'r');
    }

    /**
     * Preloads Mime and extension
     */
    protected function loadMetaInformation()
    {
        try {
            $logger = new Logger('exiftool');
            $reader = BookReader::create($logger);
            $metadataBag = $reader->files($this->file->getRealPath())->first();
            foreach ($metadataBag as $meta) {
                $tagName = $meta->getTag()->getName();
                if (0 === strcasecmp($tagName, 'MIMEType')) {
                    $this->mime = $meta->getValue()->asString();
                }
                if (0 === strcasecmp($tagName, 'FileType')) {
                    $this->ext = strtolower($meta->getValue()->asString());
                }
                if ($this->ext && $this->mime) {
                    break;
                }
            }
        } catch (\Exception $e) {
            $logger->addNotice($e->getMessage());
        }
        finally {
            $this->mime = empty($this->mime) ? $this->file->getMimeType() : $this->mime;
            $this->ext = empty($this->ext) ? $this->file->guessExtension(): $this->ext;
        }
    }

    /**
     * @return string|null
     */
    public function getMimeType()
    {
        return $this->mime;
    }

    /**
     * @return string|null
     */
    public function getExt()
    {
        return $this->ext;
    }



    /**
     * @return bool
     */
    public function delete()
    {
        return $this->file->isFile() ? unlink($this->file->getRealPath()) : false;
    }

    /**
     * @return array
     */
    public function getFileInfo()
    {
        return pathinfo($this->file->getRealPath());
    }

    public function getFilePath()
    {
        return $this->file->getRealPath();
    }
}