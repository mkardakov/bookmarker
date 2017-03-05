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

    /**
     * @param Request $req
     * @return array
     * @throws \Exception
     */
    protected function getBody(Request $req)
    {
        $data = json_decode($req->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        return $data;
    }

    /**
     * @param Request $req
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getNotEmptyBody(Request $req)
    {
        $data = $this->getBody($req);
        if (empty($data)) {
            throw new \InvalidArgumentException('Received data is incorrect');
        }
        return $data;
    }
}