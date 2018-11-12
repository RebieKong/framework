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

namespace Spark\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Framework\Di\Container;
use Spark\Framework\Di\ElementDefinition;
use Spark\Framework\Helper\DotArray;
use Spark\Framework\Interfaces\ApplicationInterface;
use Spark\Framework\Interfaces\Di\ContainerInterface;
use Spark\Framework\Interfaces\Dispatcher\DispatcherInterface;
use Spark\Framework\Interfaces\Router\RouterInterface;
use Spark\Framework\Provider\ContainerProvider;
use Spark\Framework\Router\Router;

class Application implements ApplicationInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DotArray
     */
    private $settings;

    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @var bool
     */
    private $bootstrap;

    /**
     * 应用构造方法, 这个方法接受两个参数, 分别是应用初始化容器的方法和框架默认初始化容器的Provider
     *
     * Application constructor.
     * @param callable|null $containerLoader
     * @param string $provider
     * @throws Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function __construct(callable $containerLoader = null, $provider = ContainerProvider::class)
    {
        //初始化容器, 并把容器自身的引用放入容器中
        $container = new Container();
        $container->set(
            (new ElementDefinition())
                ->setType(ContainerInterface::class)
                ->setInstance($container)
                ->setAlias('container')
        );

        //初始化ContainerProvider
        /** @var ContainerProvider $providerInstance */
        $providerInstance = new $provider;
        $container->set(
            (new ElementDefinition())
                ->setType($provider)
                ->setInstance($providerInstance)
        );
        $providerInstance->setupContainer($container);

        if (isset($containerLoader)) {
            call_user_func($containerLoader, $container);
        }

        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param callable|null $routerLoader
     * @return $this
     * @throws Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function loadRouterConfig(callable $routerLoader = null)
    {
        /** @var Router $router */
        $router = $this->container->getByAlias('router');

        if (isset($routerLoader)) {
            call_user_func($routerLoader, $router);
        }

        return $this;
    }

    /**
     * @throws Exceptions\ContainerException
     * @throws \ReflectionException
     * @throws exceptions\RouterException
     */
    public function run()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->container->getByAlias('request');

        /** @var ResponseInterface $response */
        $response = $this->container->getByAlias('response');

        /** @var RouterInterface $router */
        $router = $this->container->getByAlias('router');

        $route = $router->resolve($request->getMethod(), $request->getUri()->getPath());

        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->container->getByAlias('dispatcher');

        $callable = $dispatcher->dispatch($route, $request);

        $newResponse = call_user_func($callable, $request, $response);

        $this->respond($newResponse);
    }

    /**
     * 把Response返回到客户端
     *
     * @param ResponseInterface $response
     */
    public function respond(ResponseInterface $response)
    {
        // Send response
        if (!headers_sent()) {
            // Status
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        // Body
        if (!$this->isEmptyResponse($response)) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $chunkSize = 1024; //暂时写死

            $contentLength = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }


            if (isset($contentLength)) {
                $amountToRead = $contentLength;
                while ($amountToRead > 0 && !$body->eof()) {
                    $data = $body->read(min($chunkSize, $amountToRead));
                    echo $data;

                    $amountToRead -= strlen($data);

                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    echo $body->read($chunkSize);
                    if (connection_status() != CONNECTION_NORMAL) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * 获取返回 Response 是否为空
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isEmptyResponse(ResponseInterface $response)
    {
        if (method_exists($response, 'isEmpty')) {
            return $response->isEmpty();
        }

        return in_array($response->getStatusCode(), [204, 205, 304]);
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param $configPath
     * @return $this|ApplicationInterface
     * @throws Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function loadConfig($configPath)
    {
        if (!isset($this->configPaths[$configPath])) {
            $this->getSettings()->merge($this->readConfig($configPath));
            $this->configPaths[$configPath] = true;
        }

        return $this;
    }

    /**
     * @param $configPath
     * @return array|ApplicationInterface
     * @throws Exceptions\ContainerException
     * @throws \ReflectionException'
     */
    public function readConfig($configPath)
    {
        $config = [];
        foreach (glob($configPath.'/*.php') as $file) {
            $prefix = basename($file, '.php');
            /* @noinspection PhpIncludeInspection */
            $config[$prefix] = require $file;
        }

        $this->container->set(
            (new ElementDefinition())
            ->setType(DotArray::class)
            ->setInstance((new DotArray($config)))
            ->setSingletonScope()
            ->setAlias('config')
        );

        return $config;
    }

    /**
     * @return DotArray
     */
    public function getSettings()
    {
        if ($this->settings === null) {
            $this->settings = new DotArray();
        }

        return $this->settings;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        if (!$this->bootstrap) {
            //cli mod load commands
            if (PHP_SAPI == 'cli') {
                $this->loadCommands();
            }
            // do something

            $this->bootstrap = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadCommands()
    {
        /** @var \Symfony\Component\Console\Application $console */
        $console = $this->get(\Symfony\Component\Console\Application::class);
        $commands = $this->settings['app.commands'];
        if ($commands) {
            foreach ($commands as $command) {
                $console->add($this->get($command));
            }
        }

        return $this;
    }
}
