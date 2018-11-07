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

class Boolean extends Enum
{
    const TRUE = true;

    const FALSE = false;

    protected static $PROPERTIES = [
        'description' => [
            0 => 'No',
            1 => 'Yes',
        ],
    ];

    /**
     * @param string $value
     *
     * @return string "true", "1", 1, true = TRUE
     *                "false", "0", 0, false = FALSE
     *                other value = null
     */
    public static function valueOf($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        $name = strtoupper($value);
        if (self::hasName($name)) {
            return parent::valueOf($name);
        } elseif (in_array($name, ['0', '1'])) {
            return (bool) $value;
        } else {
            return null;
        }
    }
}
