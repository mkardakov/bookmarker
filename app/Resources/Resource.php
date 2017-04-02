<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 3:59 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\Registry;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Resource
 * @package Bookmarker\Resources
 */
abstract class Resource
{

    const SUBPARAMS_SEPARATOR = ',';

    const OPTIONS_SEPARATOR = '/';

    /**
     * @throws \Exception
     * @return array
     */
    protected function getOrdering()
    {
        $result = [];
        $req = Registry::get('request');
        if ($req->query->has('order')) {
            $rawOrder = $req->query->get('order');
            $chunks = static::parseComplexQueryParam($rawOrder);
            if (empty($chunks)) {
                throw new \Exception('Query is malformed');
            }
            foreach ($chunks as $chunk) {
                $dbField = static::convertSnakeToCamelCase($chunk[0]);
                if (!empty($dbField)) {
                    $result[$dbField] = isset($chunk[1]) ? $chunk[1] : Criteria::ASC;
                }
            }
        }
        return $result;
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

    /**
     * @return int|null
     */
    protected function getPage()
    {
        $req = Registry::get('request');
        return $req->query->has('page') ? (int)$req->query->get('page') : null;
    }

    /**
     * @return int|null
     */
    protected function getLimit()
    {
        $req = Registry::get('request');
        return $req->query->has('limit') ? (int)$req->query->get('limit') : null;
    }

    /**
     * Parse Query params like: param=level1/level2,param2=l1/l2/l3,x=3
     * @param string $query
     * @return array
     */
    final public static function parseComplexQueryParam($query)
    {
        $options = [];
        if (false !== ($subParams = explode(Resource::SUBPARAMS_SEPARATOR, $query))) {
            foreach ($subParams as $param) {
                $data = explode(Resource::OPTIONS_SEPARATOR, $param);
                if (!empty($data)) {
                    $options[] = $data;
                }
            }
        }
        return $options;
    }

    /**
     * Convert snake case string aa_bb_cc to CamelCase aaBbCc
     * @param $snakeString
     * @return string
     */
    final private static function convertSnakeToCamelCase($snakeString)
    {
        return preg_replace_callback('/([^_])_([a-z])/', function (array $matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $snakeString);
    }
}