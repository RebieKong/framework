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

final class Events
{
    const SERVER_START = 'swoole.server_start';

    const SERVER_SHUTDOWN = 'swoole.server_shutdown';

    const MANAGER_START = 'swoole.manager_start';

    const MANAGER_STOP = 'swoole.manager_stop';

    const WORKER_START = 'swoole.worker_start';

    const WORKER_STOP = 'swoole.worker_stop';

    const WORKER_ERROR = 'swoole.worker_error';

    const BEGIN_REQUEST = 'swoole.begin_request';

    const END_REQUEST = 'swoole.end_request';
}
