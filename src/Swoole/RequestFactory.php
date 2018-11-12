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

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Swoole\Http\Request;

class RequestFactory implements HttpRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRequest(Request $request)
    {
        $server = array_change_key_case($request->server, CASE_UPPER);
        $headers = $request->header;
        foreach ($headers as $key => $val) {
            $server['HTTP_'.str_replace('-', '_', strtoupper($key))] = $val;
        }
        $server['HTTP_COOKIE'] = isset($request->cookie) ? $this->cookieString($request->cookie) : '';
        $_SERVER = $server;
        $psrRequest = new ServerRequest(
            array_change_key_case($request->server, CASE_UPPER),
            isset($request->files) ? ServerRequestFactory::normalizeFiles($request->files) : [],
            ServerRequestFactory::marshalUriFromServer($server, $headers),
            $request->server['request_method'],
            'php://memory',
            $headers,
            isset($request->cookie) ? $request->cookie : [],
            isset($request->get) ? $request->get : [],
            isset($request->post) ? $request->post : null
        );
        $body = $request->rawContent();
        if ($body !== false) {
            $psrRequest->getBody()->write($body);
        }

        return $psrRequest;
    }

    private function cookieString($cookie)
    {
        $cookiestr = '';
        if ($cookie) {
            $sep = '';
            foreach ($cookie as $key => $val) {
                $cookiestr .= $sep.$key.'='.$val;
                $sep = '; ';
            }
        }

        return $cookiestr;
    }
}
