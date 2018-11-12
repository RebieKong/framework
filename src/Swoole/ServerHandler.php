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

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ServerHandler implements ServerHandlerInterface
{
    const MAX_SIZE = 2097152;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var HttpRequestFactoryInterface
     */
    private $httpRequestFactory;

    /**
     * @var array
     */
    private $options;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function onStart(Server $server)
    {
        $this->getEventDispatcher()->dispatch(Events::SERVER_START, $this->createEvent($server));
        $this->setProcessTitle('master');
    }

    public function onShutdown(Server $server)
    {
        $this->getEventDispatcher()->dispatch(Events::SERVER_SHUTDOWN, $this->createEvent($server));
    }

    public function onManagerStart(Server $server)
    {
        $this->getEventDispatcher()->dispatch(Events::MANAGER_START, $this->createEvent($server));
        $this->setProcessTitle('manager');
    }

    public function onManagerStop(Server $server)
    {
        $this->getEventDispatcher()->dispatch(Events::MANAGER_STOP, $this->createEvent($server));
    }

    public function onWorkerStart(Server $server, $workerId)
    {
        $event = $this->createEvent($server);
        $event['worker_id'] = $workerId;
        $this->getEventDispatcher()->dispatch(Events::WORKER_START, $event);
        $this->setProcessTitle($server->taskworker ? 'taskworker' : 'worker');
    }

    public function onWorkerStop(Server $server, $workerId)
    {
        $event = $this->createEvent($server);
        $event['worker_id'] = $workerId;
        $this->getEventDispatcher()->dispatch(Events::WORKER_STOP, $event);
        $this->setProcessTitle($server->taskworker ? 'taskworker' : 'worker');
    }

    public function onWorkerError(Server $server, $workerId, $workerPid, $exitCode, $signal)
    {
        $event = $this->createEvent($server);
        $event['worker_id'] = $workerId;
        $event['worker_pid'] = $workerPid;
        $event['exit_code'] = $exitCode;
        $event['signal'] = $signal;
        $this->getEventDispatcher()->dispatch(Events::WORKER_ERROR, $event);
    }

    public function onTimer(Server $server, $interval)
    {
    }

    public function onConnect(Server $server, $fd, $fromId)
    {
    }

    public function onReceive(Server $server, $fd, $fromId, $data)
    {
    }

    public function onRequest(Request $request, Response $response)
    {
        $psrRequest = $this->getHttpRequestFactory()->createRequest($request);
        $this->getEventDispatcher()->dispatch(Events::BEGIN_REQUEST, $event = new GenericEvent($psrRequest));

        /*$app = $this->container->get($this->options['application']);

        $psrResponse = is_callable($app) ? $app($psrRequest) : $app->run($psrRequest, true);

        $event['response'] = $psrResponse;
        $this->getEventDispatcher()->dispatch(Events::END_REQUEST, $event);*/
        //$this->respond($event['response'], $response);

        ob_start();
        
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = APP_PATH .'/index.php';


        $bootstrap = require APP_PATH.'/bootstrap/bootstrap.php';
        $routes = require APP_PATH.'/routes/web.php';

        $app = new \Spark\Framework\Application($bootstrap);

        // Register routes
        $app->loadConfig(APP_PATH.'/config/')
            ->bootstrap()
            ->loadRouterConfig($routes)
            ->run();
        $content = ob_get_clean();

        $response->end($content);
    }

    public function onPacket(Server $server, $data, array $clientInfo)
    {
    }

    public function onClose(Server $server, $fd, $fromId)
    {
    }

    public function onTask(Server $server, $taskId, $workId, $data)
    {
    }

    public function onFinish(Server $server, $taskId, $data)
    {
    }

    public function onPipeMessage(Server $server, $workerId, $message)
    {
    }

    protected function setProcessTitle($type)
    {
        $key = $type.'_process_title';
        if (empty($this->options[$key])) {
            $title = sprintf(
                '%s: %s http://%s:%d',
                $this->options['name'],
                $type,
                $this->options['host'],
                $this->options['port']
            );
        } else {
            $title = $this->options[$key];
        }

        @cli_set_process_title($title);
    }

    protected function getHttpRequestFactory()
    {
        if ($this->httpRequestFactory === null) {
            if ($this->container->has(HttpRequestFactoryInterface::class)) {
                $this->httpRequestFactory = $this->container->get(HttpRequestFactoryInterface::class);
            } else {
                $this->httpRequestFactory = new RequestFactory();
            }
        }

        return $this->httpRequestFactory;
    }

    protected function getEventDispatcher()
    {
        if ($this->eventDispatcher === null) {
            if ($this->container->has(EventDispatcherInterface::class)) {
                $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
            } else {
                $this->eventDispatcher = new EventDispatcher();
            }
        }

        return $this->eventDispatcher;
    }

    protected function respond(ResponseInterface $psrResponse, Response $response)
    {
        $response->status($psrResponse->getStatusCode());
        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }
        $body = $psrResponse->getBody();
        $response->header('content-length', $body->getSize());
        if ($body->getSize() > self::MAX_SIZE) {
            $file = tempnam(sys_get_temp_dir(), 'swoole');
            file_put_contents($file, (string)$body);
            $response->sendfile($file);
            @unlink($file);
        } else {
            // $response->end($body) 在 1.9.8 版出现错误
            if ($body->getSize()) {
                $response->write((string)$body);
            }
            $response->end();
        }
    }

    /**
     * @param Server $server
     *
     * @return GenericEvent
     */
    protected function createEvent(Server $server)
    {
        $event = new GenericEvent($server);
        $event['server_handler'] = $this;

        return $event;
    }
}
