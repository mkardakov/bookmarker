<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/8/17
 * Time: 10:15 PM
 */

namespace Bookmarker\Jobs\Workers;

use Pheanstalk\Job;

/**
 * Interface Worker
 * @package Bookmarker\Jobs\Workers
 */
interface Worker
{

    public function accept(Job $job);
}