<?php
namespace Swoole\Log;
use Swoole;

class EchoLog extends Swoole\Log implements Swoole\IFace\Log
{
    protected $display = true;

    function __construct($conf)
    {
        if (isset($conf['display']) and $conf['display'] == false)
        {
            $this->display = false;
        }
    }

    function put($msg, $level = self::INFO)
    {
        if ($this->display)
        {
            $log = $this->format($msg, $level);
            if ($log) echo $log;
        }
    }
}