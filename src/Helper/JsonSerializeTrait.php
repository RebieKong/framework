<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */

namespace Spark\Framework\Helper;

/**
 * Helper class for implements JsonSerializable.
 */
trait JsonSerializeTrait
{
    public function jsonSerialize()
    {
        return self::toArray();
    }

    public function toArray($keyStyle = null, $excludedKeys = [])
    {
        $vars = get_object_vars($this);
        if (isset($keyStyle)) {
            if ($keyStyle === 'camelize') {
                $vars = Arrays::mapKeys($vars, function ($key) {
                    return lcfirst(Text::camelize($key));
                });
            } elseif ($keyStyle === 'uncamelize') {
                $vars = Arrays::mapKeys($vars, [Text::class, 'uncamelize']);
            }
        }
        if (!empty($excludedKeys)) {
            foreach ($excludedKeys as $key) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }
}
