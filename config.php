<?php
$config['server'] = array(
    //监听的HOST
    'host' => '0.0.0.0',
    //监听的端口
    'port' => '9505',
    //WebSocket的URL地址，供浏览器使用的
    'url' => 'ws://127.0.0.1:9505',
    'origin' => 'http://coollive.labake.cn',
);

$config['swoole'] = array(
    'log_file' => __DIR__.'/log/swoole.log',
    'worker_num' => 4,
	//不要修改这里
    'max_request' => 0,
    'task_worker_num' => 1,
    //是否要作为守护进程
    'daemonize' => 0,
);

$config['webim'] = array(
    'data_dir' => __DIR__.'/data/',
    'log_file' => __DIR__.'/log/webim.log',
);

return $config;
