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

use Spark\Framework\Helper\DataDumper;
use Spark\Framework\Tests\TestCase;

class DataDumperTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function testLoad($data, $content, $format)
    {
        $this->assertEquals(
            $data,
            DataDumper::load($content, $format)
        );
    }

    /**
     * @dataProvider data
     */
    public function testDump($data, $content, $format)
    {
        $this->assertEquals(
            $content,
            trim(DataDumper::dump($data, $format, false))
        );
    }

    public function data()
    {
        return [
            [[1], '[1]', 'json'],
            [[1], '- 1', 'yaml'],
            [[1], "array (\n  0 => 1,\n)", 'php'],
        ];
    }
}
