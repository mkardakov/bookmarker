<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/4/17
 * Time: 10:15 PM
 */

namespace Bookmarker\MetadataProcessor\Exiftool;


use Bookmarker\Registry;
use PHPExiftool\Exiftool;

class BookExiftool extends Exiftool
{

    /**
     * @return mixed
     * @throws \ErrorException
     */
    protected static function getBinary()
    {
        $config = Registry::get('app')['config'][APP_ENV]['meta_data'];
        if (!isset($config['exiftool_binary_path']) || !is_executable($config['exiftool_binary_path'])) {
            throw new \ErrorException(sprintf(
                'exiftool not configured correctly. Check path %s',
                $config['exiftool_binary_path']
            ));
        }
        return $config['exiftool_binary_path'];
    }
}