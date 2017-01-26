<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/5/17
 * Time: 5:50 PM
 */

namespace Bookmarker;

/**
 * Class Registry
 * @package Bookmarker
 */
final class Registry
{
    /**
     * @var array
     */
    protected static $values = array();

    /**
     * Registry constructor.
     */
    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$values[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (!self::has($key)) {
            throw new \OutOfBoundsException("Registry does not contain key = $key");
        }
        return self::$values[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        return array_key_exists($key, self::$values);
    }
}