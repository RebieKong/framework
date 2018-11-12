<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */

namespace Spark\Framework\Swoole;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;

interface HttpRequestFactoryInterface
{
    /**
     * @param Request $request
     *
     * @return ServerRequestInterface
     */
    public function createRequest(Request $request);
}
