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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            Events::SERVER_START => 'onServerStart',
            Events::SERVER_SHUTDOWN => 'onServerShutdown',
            Events::MANAGER_START => 'onManagerStart',
            Events::MANAGER_STOP => 'onManagerStop',
            Events::WORKER_START => 'onWorkerStart',
            Events::WORKER_STOP => 'onWorkerStop',
            Events::WORKER_ERROR => 'onWorkerError',
            Events::BEGIN_REQUEST => 'onBeginRequest',
            Events::END_REQUEST => 'onEndRequest',
        ];
    }

    public function onServerStart($event)
    {
        $server = $event->getSubject();
        $this->logger->info(
            sprintf('[SwooleServer] start pid=%d', $server->master_pid),
            ['setting' => $server->setting]
        );
    }

    public function onServerShutdown($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf('[SwooleServer] shutdown pid=%d', $server->master_pid));
        @unlink($server->setting['pid_file']);
    }

    public function onWorkerStart($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf(
            '[SwooleServer] start worker pid=%d worker_id=%d',
            $server->worker_pid,
            $event['worker_id']
        ));
    }

    public function onWorkerStop($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf(
            '[SwooleServer] stop worker pid=%d worker_id=%d',
            $server->worker_pid,
            $event['worker_id']
        ));
    }

    public function onWorkerError($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf(
            '[SwooleServer] worker exit unexpected pid=%d worker_id=%d signal=%d',
            $event['worker_pid'],
            $event['worker_id'],
            $event['signal']
        ));
    }

    public function onManagerStart($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf('[SwooleServer] manager start pid=%d', $server->manager_pid));
    }

    public function onManagerStop($event)
    {
        $server = $event->getSubject();
        $this->logger->info(sprintf('[SwooleServer] manager stop pid=%d', $server->manager_pid));
    }

    public function onBeginRequest()
    {
        $this->logger->debug('[SwooleServer] start request');
    }

    public function onEndRequest()
    {
        $this->logger->debug('[SwooleServer] end request');
    }
}
