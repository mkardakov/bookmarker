<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/6/17
 * Time: 12:41 PM
 */

namespace Bookmarker\Responses;

use Bookmarker\Registry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreatedResponse
 * @package Bookmarker\Responses
 */
class CreatedResponse extends Response
{
    const STATUS = 201;

    /**
     * CreatedResponse constructor.
     * @param mixed|string $uri to created resources after successful POST request
     * @param int $status
     * @param array $headers
     */
    public function __construct($uri, $status = CreatedResponse::STATUS, array $headers = array())
    {
        $app = Registry::get('app');
        $content = $app['serializer']->serialize(array(
            "contentUri" => $uri
        ), RESPONSE_FORMAT);
        parent::__construct($content, self::STATUS, $headers);
    }
}