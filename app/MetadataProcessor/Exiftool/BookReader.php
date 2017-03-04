<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/23/17
 * Time: 7:15 PM
 */

namespace Bookmarker\MetadataProcessor\Exiftool;

use PHPExiftool\RDFParser;
use PHPExiftool\Reader;
use Psr\Log\LoggerInterface;

/**
 * Class BookReader
 * @package Bookmarker\MetadataProcessor\Exiftool
 */
class BookReader extends Reader
{

    /**
     * @var bool
     */
    protected $withEmbedded = false;


    /**
     * @return string
     */
    protected function buildQuery()
    {
        $query = parent::buildQuery();
        switch (true) {
            case $this->getWithEmbedded():
                $query .= ' -ee';
                break;
            default:
                break;
        }
        return $query;
    }

    /**
     * @return bool
     */
    public function getWithEmbedded()
    {
        return $this->withEmbedded;
    }

    /**
     * @param bool $withEmbedded
     * @return $this;
     */
    public function setWithEmbedded($withEmbedded)
    {
        $this->withEmbedded = $withEmbedded;
        return $this;
    }

    public static function create(LoggerInterface $logger)
    {
        return new static(new BookExiftool($logger), new RDFParser());
    }

}