<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/8/17
 * Time: 10:16 PM
 */

namespace Bookmarker\Jobs\Workers;

use Bookmarker\Db\Entities\Book;
use Bookmarker\Db\Entities\File;
use Bookmarker\FileDrivers\LocalDriver;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pimple\Container;
use Silex\Application;

/**
 * Class ConvertWorker
 * @package Bookmarker\Jobs\Workers
 */
class ConvertWorker implements Worker
{

    protected $output;

    protected $container;

    /**
     * ConvertWorker constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->container = $app;
    }

    /**
     * @param Job $job
     * @throws \ErrorException
     * @throws \Exception
     */
    public function accept(Job $job)
    {

        $data = json_decode($job->getData(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        if (!isset($data['book_id'], $data['to'])) {
            throw new \InvalidArgumentException('incorrect job format  received');
        }
        $book = $this->container['orm.em']->find('doctrine:Book', $data['book_id']);
        $filesSet = $book->getFiles();
        if ($filesSet->isEmpty()) {
            throw new \ErrorException('There are no files available for this book');
        }
        if ($book instanceof Book) {
            $existFile = $filesSet->first();
            $toFile = $this->generateOutFileName($existFile, $data['to']);
            $this->container['logger']->addDebug(sprintf('Job accepted to process. Format to convert %s', $data['to']));
            exec("/usr/bin/ebook-convert {$existFile->getFilePath()} {$toFile}", $output, $status);
            if (!is_file($toFile)) {
                throw new \Exception(sprintf('Conversion failed. File %s will not be available', $data['to']));
            }
            $newFileEntity = clone $existFile;
            $newFileEntity->setFilePath($toFile);
            $newFileEntity->setExt($data['to']);
            $driver = new LocalDriver($toFile, true);
            $newFileEntity->setMime($driver->getMimeType());
            $this->container['orm.em']->persist($newFileEntity);
            $this->container['orm.em']->flush();
        } else {
            throw new \Exception('book not found');
        }
        $this->container['logger']->addDebug(
            sprintf('ebook-convert: %s', print_r($output, 1))
        );
        $this->container['logger']->addDebug(
            sprintf(
                'Worker with pid %s finished. book: %d, format: %s',
                getmypid(),
                $data['book_id'],
                $data['to']
            ));

    }

    /**
     * @param $output
     * @return $this
     */
    protected function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param File $fromFile
     * @param $to
     * @return mixed
     */
    protected function generateOutFileName(File $fromFile, $to)
    {
        $path = $fromFile->getFilePath();
        $ext = $fromFile->getExt();
        return str_replace($ext, $to, $path);
    }

}