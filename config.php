<?php
$config['server'] = array(
    //������HOST
    'host' => '0.0.0.0',
    //�����Ķ˿�
    'port' => '9505',
    //WebSocket��URL��ַ���������ʹ�õ�
    'url' => 'ws://42.120.4.50:9505',
    'origin' => 'http://coollive.labake.cn',
);

$config['swoole'] = array(
    'log_file' => __DIR__.'/log/swoole.log',
    'worker_num' => 4,
	//��Ҫ�޸�����
    'max_request' => 0,
    'task_worker_num' => 1,
    //�Ƿ�Ҫ��Ϊ�ػ�����
    'daemonize' => 0,
);

$config['webim'] = array(
    'data_dir' => __DIR__.'/data/',
    'log_file' => __DIR__.'/log/webim.log',
);

return $config;
