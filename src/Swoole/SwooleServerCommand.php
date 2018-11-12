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

use Spark\Framework\Command\BaseCommand;
use Spark\Framework\Di\ElementDefinition;
use Spark\Framework\Interfaces\ApplicationInterface;
use Spark\Framework\Interfaces\Di\ContainerInterface;
use Swoole\Http\Server as HttpServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SwooleServerCommand extends BaseCommand
{
    /**
     * @var \Swoole\Server
     */
    private $server;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected static $SWOOLE_EVENTS = [
        'start',
        'shutdown',
        'workerStart',
        'workerStop',
        'workerError',
        //'timer',
        'request',
        'packet',
        'close',
        'task',
        'finish',
        'pipeMessage',
        'managerStart',
        'managerStop',
    ];

    protected static $SWOOLE_OPTIONS = [
        'backlog',
        'buffer_output_size',
        'chroot',
        'cpu_affinity_ignore',
        'daemonize',
        'discard_timeout_request',
        'dispatch_mode',
        'enable_reuse_port',
        'enable_unsafe_event',
        'group',
        'heartbeat_check_interval',
        'heartbeat_idle_time',
        'http_parse_post',
        'upload_tmp_dir',
        'log_file',
        'log_level',
        'max_conn',
        'max_connection',
        'max_request',
        'message_queue_key',
        'open_cpu_affinity',
        'open_eof_check',
        'open_eof_split',
        'open_length_check',
        'open_tcp_nodelay',
        'package_eof',
        'package_length_type',
        'package_max_length',
        'pid_file',
        'pipe_buffer_size',
        'reactor_num',
        'ssl_cert_file',
        'ssl_ciphers',
        'ssl_method',
        'task_ipc_mode',
        'task_max_request',
        'task_tmpdir',
        'task_worker_max',
        'task_worker_num',
        'tcp_defer_accept',
        'user',
        'worker_num',
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    protected function configure()
    {
        if (!$this->getName()) {
            $this->setName('server');
        }
        $this->setDescription('Swoole server management')
            ->addOption('start', null, null, 'start swoole server')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'server host')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'server port')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'server option name', 'http')
            ->addOption('stop', null, null, 'stop swoole server')
            ->addOption('reload', null, null, 'reload swoole server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!extension_loaded('swoole')) {
            $output->writeln('<error> require swoole extension </>');
            exit;
        }
        $this->bootSwoole();
        if ($input->getOption('stop')) {
            $this->stop($input, $output);
        } elseif ($input->getOption('reload')) {
            $this->reload($input, $output);
        } else {
            $this->start($input, $output);
        }
    }

    private function bootSwoole()
    {
        $this->container->set((
        (new ElementDefinition())
        ->setType(HttpRequestFactoryInterface::class)
            ->setInstance(new RequestFactory())
        ));
    }

    public function start($input, $output)
    {
        $options = $this->getServerOptions($input);
        foreach (['host' => 'localhost', 'port' => 80] as $name => $default) {
            if ($input->getOption($name)) {
                $options[$name] = $input->getOption($name);
            } elseif (empty($options[$name])) {
                $options[$name] = $default;
            }
        }
        $this->server = $server = new HttpServer($options['host'], $options['port']);
        $output->writeln("<info>Listening on http://{$options['host']}:{$options['port']}</>");
        $handler = $this->container->get(isset($options['server_handler']) ? $options['server_handler'] : ServerHandler::class);
        $handler->setOptions($options);

        foreach (self::$SWOOLE_EVENTS as $event) {
            $server->on($event, [$handler, 'on'.ucfirst($event)]);
        }
        $server->set(array_intersect_key($options, array_flip(self::$SWOOLE_OPTIONS)));
        $server->start();
    }

    public function stop($input, $output)
    {
        $pid = $this->getMasterPid($input);
        if (!$pid) {
            $output->writeln('<info>Swoole server was not started</>');

            return -1;
        }
        posix_kill($pid, SIGTERM);
    }

    public function reload($input, $output)
    {
        $pid = $this->getMasterPid($input);
        if (!$pid) {
            $output->writeln('<info>Swoole server was not started</>');

            return -1;
        }
        posix_kill($pid, SIGUSR1);
    }

    protected function getMasterPid($input)
    {
        $options = $this->getServerOptions($input);
        if (is_readable($options['pid_file'])) {
            $pid = file_get_contents($options['pid_file']);
            if (is_numeric($pid) && posix_kill($pid, 0)) {
                return $pid;
            }
        }
    }

    protected function getServerOptions($input)
    {
        $name = sprintf('swoole_%s_server', $input->getOption('name'));
        $options = $this->container->getByAlias('config')['app.'.$name];
        if (empty($options['name'])) {
            $options['name'] = $name;
        }
        if (empty($options['pid_file'])) {
            $path = $this->container->getByAlias('config')['app.runtime_path'];
            if (!is_dir($path) && !mkdir($path, 0777, true)) {
                throw new \RuntimeException("Cannot create runtime path '{$path}'");
            }
            $options['pid_file'] = sprintf('%s/swoole.%s.pid', $path, md5($options['name']));
        }
        if (empty($options['application'])) {
            $options['application'] = ApplicationInterface::class;
        }
        if (!$this->container->has($options['application'])) {
            throw new \RuntimeException(sprintf(
                "Configuration '%s' was not found in current service definitions",
                $options['application']
            ));
        }

        return $options;
    }
}
