<?php
declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */

namespace Spark\Framework\Exceptions;

use \Exception;
use Psr\Container\NotFoundExceptionInterface;

class ContainerException extends Exception implements NotFoundExceptionInterface
{
}
