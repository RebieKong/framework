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

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;

interface ServerHandlerInterface
{
    /**
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Gets the option value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name);

    public function onStart(Server $server);

    public function onShutdown(Server $server);

    public function onWorkerStart(Server $server, $workerId);

    public function onWorkerStop(Server $server, $workerId);

    public function onWorkerError(Server $server, $workerId, $workerPid, $exitCode, $signal);

    public function onTimer(Server $server, $interval);

    public function onConnect(Server $server, $fd, $fromId);

    public function onReceive(Server $server, $fd, $fromId, $data);

    public function onRequest(Request $request, Response $response);

    public function onPacket(Server $server, $data, array $clientInfo);

    public function onClose(Server $server, $fd, $fromId);

    public function onTask(Server $server, $taskId, $workId, $data);

    public function onFinish(Server $server, $taskId, $data);

    public function onPipeMessage(Server $server, $workerId, $message);

    public function onManagerStart(Server $server);

    public function onManagerStop(Server $server);
}
