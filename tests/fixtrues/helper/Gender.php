<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */
namespace Spark\Framework\Tests\fixtrues\helper;

use Spark\Framework\Helper\Enum;

class Gender extends Enum
{
    const MALE = 'm';

    const FEMALE = 'f';

    protected static $PROPERTIES = [
        'description' => [
            self::MALE => '男',
            self::FEMALE => '女'
        ],
        'ordinal' => [
            self::MALE => 1,
            self::FEMALE => 2
        ],
        'enName' => [
            self::MALE => 'male'
        ],
    ];
}
