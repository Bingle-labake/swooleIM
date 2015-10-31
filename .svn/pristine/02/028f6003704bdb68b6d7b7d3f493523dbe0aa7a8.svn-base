<?php
define('DEBUG', 'on');
define('WEBPATH', dirname(__DIR__));
require WEBPATH . '/libs/lib_config.php';

$AppSvr = new Swoole\Protocol\SOAServer;
$AppSvr->setLogger(new \Swoole\Log\EchoLog(true)); //Logger

/**
 * 注册一个自定义的命名空间到SOA服务器
 * 默认使用 apps/classes
 */
$AppSvr->addNameSpace('BL', __DIR__ . '/class');

Swoole\Error::$echo_html = false;
$server = Swoole\Network\Server::autoCreate('0.0.0.0', 8888);
$server->setProtocol($AppSvr);
//$server->daemonize(); //作为守护进程
$server->run(
    array(
        //TODO： 实际使用中必须调大进程数
        'worker_num'            => 4,

        'max_request'           => 5000,
        'dispatch_mode'         => 3,
        'open_length_check'     => 1,
        'package_max_length'    => $AppSvr->packet_maxlen,
        'package_length_type'   => 'N',
        'package_body_offset'   => \Swoole\Protocol\SOAServer::HEADER_SIZE,
        'package_length_offset' => 0,
    )
);
