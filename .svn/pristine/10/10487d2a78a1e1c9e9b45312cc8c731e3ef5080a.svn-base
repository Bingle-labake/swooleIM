<?php
namespace Swoole\Database;
/**
 * PDO数据库封装类
 * @package SwooleExtend
 * @author Tianfeng.Han
 *
 */
class PdoDB extends \PDO
{
	public $debug = false;
    protected $config;

	function __construct($db_config)
	{
        $this->config = $db_config;
	}

	function connect()
	{
        $db_config = &$this->config;
        $dsn = $db_config['dbms'].":host=".$db_config['host'].";dbname=".$db_config['name'];

        if (!empty($db_config['persistent']))
        {
            parent::__construct($dsn, $db_config['user'], $db_config['passwd'], array(\PDO::ATTR_PERSISTENT => true));
        }
        else
        {
            parent::__construct($dsn, $db_config['user'], $db_config['passwd']);
        }
        if ($db_config['setname']) parent::query('set names ' . $db_config['charset']);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
	}

    function quote($str)
    {
        return trim(parent::quote($str), '\'');
    }

	/**
	 * 执行一个SQL语句
	 * @param string $sql 执行的SQL语句
     * @return \PDOStatement
     */
	public final function query($sql)
	{
        if ($this->debug)
        {
            echo "$sql<br />\n<hr />";
        }
        $res = parent::query($sql) or \Swoole\Error::info(
            "SQL Error",
            implode(", ", $this->errorInfo()) . "<hr />$sql"
        );
        return $res;
	}
	/**
	 * 关闭连接，释放资源
	 * @return null
	 */
	function close()
	{
		unset($this);
	}
}
