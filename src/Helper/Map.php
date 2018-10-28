<?php
/**
 * Created by PhpStorm.
 * User: wujunze
 * Date: 2018/10/27
 * Time: 7:28 PM
 */

namespace Spark\Framework\Helper;


use ArrayAccess;

/**
 * Class Map
 * @package Spark\Framework\Helper
 */
class Map implements ArrayAccess
{
    private $input;

    public function __construct(array $input)
    {
        $this->input = $input;
    }

    public function get($key, $defaultValue = null)
    {
        if (isset($this->input[$key])) {
            return $this->input[$key];
        } else {
            return $defaultValue;
        }
    }

    public function getAll()
    {
        return $this->input;
    }

    public function offsetExists($offset)
    {
        if (isset($this->input[$offset])) {
            return true;
        } else {
            return false;
        }
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return null;
    }

    public function offsetUnset($offset)
    {
        return null;
    }
}