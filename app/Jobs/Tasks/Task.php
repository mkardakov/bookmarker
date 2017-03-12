<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/8/17
 * Time: 9:49 PM
 */

namespace Bookmarker\Jobs\Tasks;

/**
 * Interface Task
 * @package Bookmarker\Jobs\Tasks
 */
interface Task
{
    public function send();
}