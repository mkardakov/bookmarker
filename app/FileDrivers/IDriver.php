<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 7:25 PM
 */

namespace Bookmarker\FileDrivers;

/**
 * Interface IDriver
 * @package Bookmarker\FileDrivers
 */
interface IDriver
{

    const DEFAULT_MIME_TYPE = 'application/octet-stream';

    public function store();

    public function getDownloadLink();

    public function getMimeType();

    public function delete();

    public function getFileInfo();

    public function getFilePath();
}
