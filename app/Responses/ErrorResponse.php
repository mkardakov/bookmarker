<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/6/17
 * Time: 12:46 PM
 */

namespace Bookmarker\Responses;

use Bookmarker\Registry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorResponse
 * @package Bookmarker\Responses
 */
class ErrorResponse extends Response
{
    const STATUS = 400;

    public function __construct($content, $status = ErrorResponse::STATUS, array $headers = array(), $errCode = 0)
    {
        $app = Registry::get('app');
        $content = $app['serializer']->serialize(array(
            "error_msg" => $content,
            "error_code" => $errCode
        ), RESPONSE_FORMAT);
        parent::__construct($content, $status, $headers);
    }
}