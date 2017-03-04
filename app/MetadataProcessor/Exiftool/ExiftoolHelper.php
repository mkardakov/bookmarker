<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/23/17
 * Time: 11:18 PM
 */

namespace Bookmarker\MetadataProcessor\Exiftool;

use Bookmarker\MetadataProcessor\DataSet\Storage;
use Monolog\Logger;
/**
 * Class ExiftoolHelper
 * @package Bookmarker\MetadataProcessor\Exiftool
 */
trait ExiftoolHelper
{

    /**
     * @var array
     */
    protected static $supportedMetaKeys = array(
        'Title' => true,
        'Author' => true,
        'CreateDate' => true,
        'Language' => true,
        'Description' => true,
    );

    /**
     * Parse exiftool output
     * @param $filePath
     * @return Storage
     */
    public function parse($filePath)
    {
        $logger = new Logger('exiftool');
        $reader = BookReader::create($logger);
        $metadataBag = $reader->files($filePath)->first();

        $storage = new Storage();
        foreach ($metadataBag as $metadata) {
            $tagName = $metadata->getTag()->getName();
            if (isset(static::$supportedMetaKeys[$tagName])) {
                $value = $metadata->getValue()->asString();
                switch($tagName) {
                    case 'Title':
                        if ($title = $this->getTitleFromRaw($value)) {
                            $storage->setTitle($title);
                        }
                        break;
                    case 'Language':
                        if ($lang = $this->getLangFromRaw($value)) {
                            $storage->setLang($lang);
                        }
                        break;
                    case 'CreateDate':
                        if ($year = $this->getYearFromRaw($value)) {
                            $storage->setYear($year);
                        }
                        break;
                    case 'Author':
                        if ($authors = $this->getAuthorsFromRaw($value)) {
                            foreach ($authors as $author) {
                                $storage->setAuthor($author);
                            }
                        }
                        break;
                    case 'Description':
                        if ($description = $this->getDescriptionFromRaw($value)) {
                            $storage->setDescription($description);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $storage;
    }

    /**
     * Return parsed year if succeed
     * @param string $date
     * @return bool|int
     */
    protected function getYearFromRaw($date)
    {
        $result = false;
        if (preg_match('/(?:^|\D)(?<year>[12]\d{3})(?:\D|$)/', $date, $matches)) {
            $result = $matches['year'];
        }
        return $result;
    }

    /**
     * @param $lang
     * @return bool|string
     */
    protected function getLangFromRaw($lang)
    {
        $result = false;
        $lang = trim($lang);
        if (preg_match('/^[a-z]{2}/i', $lang, $matches)) {
            $result = strtolower($matches[0]);
        }
        return $result;
    }

    /**
     * @param $authors
     * @return array|bool
     */
    protected function getAuthorsFromRaw($authors)
    {
        preg_match_all('/\p{L}{2,}/iu', $authors, $matches);
        return !empty($matches[0]) ? $matches[0] : false;
    }

    /**
     * @param $title
     * @return mixed
     */
    protected function getTitleFromRaw($title)
    {
        return $title;
    }

    /**
     * @param $description
     * @return mixed
     */
    protected function getDescriptionFromRaw($description)
    {
        return $description;
    }
}