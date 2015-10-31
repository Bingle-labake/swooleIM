<?php
define('DEBUG', 'on');
define('WEBPATH', dirname(__DIR__));
require __DIR__ . '/../libs/lib_config.php';
//require __DIR__ . '/../vendor/autoload.php';

$cloud = new Swoole\Client\SOA;
$cloud->addServers(array('127.0.0.1:8888'));

$s = microtime(true);
$ok = $err = 0;
for ($i = 0; $i < 1; $i++)
{
    $s2 = microtime(true);
    $ret1 = $cloud->task("BL\\Test::test1", "hello{$i}_1", function($retObj) {
        echo "task1 finish\n";
    });
    $ret2 = $cloud->task("BL\\Test::hello");
    $ret3 = $cloud->task("BL\\Test::test1", "hello{$i}_3");
    $ret4 = $cloud->task("BL\\Test::test1", "hello{$i}_4");
    $ret5 = $cloud->task("App\\Test::test1");
    $ret6 = $cloud->task("BL\\Test::test1", "hello{$i}_6");
    $ret7 = $cloud->task("BL\\Test::test1", "hello{$i}_7");
    $ret8 = $cloud->task("BL\\Test::test1", "hello{$i}_8");
    echo "send " . (microtime(true) - $s2) * 1000, "\n";

    $n = $cloud->wait(0.5); //500ms超时
    //表示全部OK了
    if ($n === 8)
    {
        var_dump($ret1->data, $ret2->data, $ret3->data, $ret4->data, $ret5->data, $ret6->data, $ret7->data, $ret8->data);
        echo "finish\n";
        $ok++;
    }
    else
    {
        echo "#{$i} \t";
        echo $ret1->code . '|' . $ret2->code . '|' . $ret3->code . '|' . $ret4->code . '|' . $ret5->code . '|' . $ret6->code . '|' . $ret7->code . '|' . $ret8->code . '|' . "\n";
        $err++;
        exit;
    }
    unset($ret1, $ret2, $ret3, $ret4, $ret5, $ret6, $ret7, $ret8);
}
echo "failed=$err.\n";
echo "success=$ok.\n";
echo "use " . (microtime(true) - $s) * 1000, "ms\n";
unset($cloud, $ret1, $ret2);
