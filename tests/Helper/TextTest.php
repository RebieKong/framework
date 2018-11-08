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

use Spark\Framework\Helper\Text;
use Spark\Framework\Tests\TestCase;

class TextTest extends TestCase
{
    /**
     * @dataProvider camelizeCase
     */
    public function testCamelize($str, $delimiter, $expect)
    {
        $this->assertEquals($expect, Text::camelize($str, $delimiter));
    }

    public function camelizeCase()
    {
        return [
            ['coco_bongo', null, 'CocoBongo'],
            ['co_co-bon_go', '-', 'Co_coBon_go'],
            ['co_co-bon_go', '-_', 'CoCoBonGo']
        ];
    }

    /**
     * @dataProvider uncamelizeCase
     */
    public function testUncamelize($str, $delimiter, $expect)
    {
        $this->assertEquals($expect, Text::uncamelize($str, $delimiter));
    }

    public function uncamelizeCase()
    {
        return [
            ['CocoBongo', null, 'coco_bongo'],
            ['CocoBongo', '-', 'coco-bongo'],
        ];
    }

    /**
     * @dataProvider startsWithCase
     */
    public function testStartsWith($str, $needle, $case, $expect)
    {
        $this->assertEquals($expect, Text::startsWith($str, $needle, $case));
    }

    public function startsWithCase()
    {
        return [
            ['Hello', 'He', true, true],
            ['Hello', 'he', false, false],
            ['Hello', 'he', true, true]
        ];
    }

    /**
     * @dataProvider endsWithCase
     */
    public function testEndsWith($str, $needle, $case, $expect)
    {
        $this->assertEquals($expect, Text::endsWith($str, $needle, $case));
    }

    public function endsWithCase()
    {
        return [
            ['Hello', 'llo', true, true],
            ['Hello', 'LLO', false, false],
            ['Hello', 'LLO', true, true]
        ];
    }

    public function testLower()
    {
        $this->assertEquals('hello', Text::lower('HELLO'));
    }

    public function testUpper()
    {
        $this->assertEquals('HELLO', Text::upper('hello'));
    }
}
