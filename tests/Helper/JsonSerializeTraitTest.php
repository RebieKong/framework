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

use Spark\Framework\Tests\fixtrues\helper\JsonObject;
use Spark\Framework\Tests\TestCase;

/**
 * TestCase for Enum
 */
class JsonSerializeTraitTest extends TestCase
{
    public function test()
    {
        $obj = new JsonObject(1, 'foo');
        $this->assertEquals('{"user_id":1,"userName":"foo"}', json_encode($obj));

        $this->assertEquals('{"userId":1,"userName":"foo"}', json_encode($obj->toArray('camelize')));

        $this->assertEquals('{"user_id":1,"user_name":"foo"}', json_encode($obj->toArray('uncamelize')));
    }
}
