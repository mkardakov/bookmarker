<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 3:59 PM
 */

namespace Bookmarker\Resources;
use Bookmarker\Registry;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Resource
 * @package Bookmarker\Resources
 */
abstract class Resource
{

    /**
     * @param Request $req
     * @return int
     */
    protected function getMaxRowsNumber(Request $req)
    {
        $app = Registry::get('app');
        $maxCount = $actual = $app['config'][APP_ENV]['max_record_number'];
        if ($req->query->has('record_number')) {
            $actual = (int)$req->get('record_number');
            if ($actual > $maxCount || $actual <= 0) {
                $actual = $maxCount;
            }
        }
        return $actual;
    }
}