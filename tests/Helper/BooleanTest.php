<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */
namespace Spark\Framework\Tests\Helper;

use Spark\Framework\Helper\Boolean;
use Spark\Framework\Tests\TestCase;

class BooleanTest extends TestCase
{
    /**
     * @dataProvider booleans
     */
    public function testValueOf($value, $expect)
    {
        $val = Boolean::valueOf((string)$value);
        // var_export([$value, $val, $expect]);
        $this->assertEquals($val, $expect);
    }

    public function booleans()
    {
        return [
            [1, true],
            ['1', true],
            ['true', true],
            [true, true],
            [0, false],
            ['0', false],
            ['false', false],
            [false, false],
            ['t', null],
            [2, null]
        ];
    }
}
