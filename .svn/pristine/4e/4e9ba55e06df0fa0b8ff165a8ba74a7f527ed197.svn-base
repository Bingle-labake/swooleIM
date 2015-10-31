<?php
namespace Swoole\Http;

/**
 * Class Http_LAMP
 * @package Swoole
 */
class PWS implements \Swoole\IFace\Http
{
    function header($k, $v)
    {
        $k = ucwords($k);
        \Swoole::$php->response->setHeader($k, $v);
    }

    function status($code)
    {
        \Swoole::$php->response->setHttpStatus($code);
    }

    function response($content)
    {
        $this->finish($content);
    }

    function redirect($url, $mode = 301)
    {
        \Swoole::$php->response->setHttpStatus($mode);
        \Swoole::$php->response->setHeader('Location', $url);
    }

    function finish($content = null)
    {
        \Swoole::$php->request->finish = 1;
        if($content) \Swoole::$php->response->body = $content;
        throw new \Exception;
    }
}
