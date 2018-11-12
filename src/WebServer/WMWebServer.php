<?php declare(strict_types=1);
/**
 * This file is part of Spark Framework.
 *
 * @link     https://github.com/spark-php/framework
 * @document https://github.com/spark-php/framework
 * @contact  itwujunze@gmail.com
 * @license  https://github.com/spark-php/framework
 */

namespace Spark\Framework\WebServer;

use Workerman\Protocols\Http;

class WMWebServer extends \Workerman\WebServer
{
    const INDEX_FILE = 'index.php';

    /**
     *  rewrite http request
     *
     * @param \Workerman\Connection\TcpConnection $connection
     *
     * @return mixed
     */
    public function onMessage($connection)
    {
        // REQUEST_URI.
        $urlInfo = parse_url($_SERVER['REQUEST_URI']);
        if (!$urlInfo) {
            Http::header('HTTP/1.1 400 Bad Request');
            $connection->close('<h1>400 Bad Request</h1>');

            return;
        }
        $rootDir = $this->serverRoot[$_SERVER['SERVER_NAME']] ?: current($this->serverRoot);

        $index = $rootDir['root'].'/' . self::INDEX_FILE;

        if (!file_exists($index)) {
            Http::header('HTTP/1.1 404 Not Found');
            $connection->close('<h1>404 Not Found</h1>');

            return;
        }
        $indexFile = realpath($index);
        $wmCwd = getcwd();
        chdir($rootDir['root']);
        ini_set('display_errors', 'off');
        ob_start();
        // Try to include php file.
        try {

            // $_SERVER.
            $_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
            $_SERVER['REMOTE_PORT'] = $connection->getRemotePort();
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['SCRIPT_FILENAME'] = $rootDir['root'].'/index.php';


            include $indexFile;
        } catch (\Exception $e) {
            // Jump_exit?
            if ($e->getMessage() != 'jump_exit') {
                echo $e;
            }
        }
        $content = ob_get_clean();

        Http::header('Content-Type: application/json;charset=utf-8');
        Http::header('X-Powered-By: SparkPHP', true);

        if (strtolower($_SERVER['HTTP_CONNECTION']) === 'keep-alive') {
            $connection->send($content);
        } else {
            $connection->close($content);
        }
        chdir($wmCwd);
    }
}
