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

use Spark\Framework\Helper\JsonSerializeTrait;

class JsonObject implements \JsonSerializable
{
    use JsonSerializeTrait;

    private $user_id;

    private $userName;

    public function __construct($id, $name)
    {
        $this->user_id = $id;
        $this->userName = $name;
    }
}
