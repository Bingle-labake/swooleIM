<?php
namespace WebIM\Store;

class Redis
{
    /**
     * @var \redis
     */
    protected $redis;

    static $prefix = "im_user_";
	
	protected $history = array();
    protected $history_max_size = 100;
    protected $history_write_count = 0;

    function __construct($host = '127.0.0.1', $port = 6379, $timeout = 0.0) {
        $redis = new \redis;
        $redis->connect($host, $port, $timeout);
        $this->redis = $redis;
    }

    function login($client_id, $room_id, $info) {
        $this->redis->set(self::$prefix.'client_'.$client_id, serialize($info));
        $this->redis->sAdd(self::$prefix.'online_'.$room_id, $client_id);
    }

    function logout($client_id, $room_id)
    {
        $this->redis->delete(self::$prefix.'client_'.$client_id);
        $this->redis->sRemove(self::$prefix.'online_'.$room_id, $client_id);
    }

    function getOnlineUsers($room_id)
    {
        return $this->redis->sMembers(self::$prefix.'online_'.$room_id);
    }

    function getUsers($users)
    {
        $keys = array();
        $ret = array();

        foreach($users as $v)
        {
            $keys[] = self::$prefix.'client_'.$v;
        }

        $info = $this->redis->mget($keys);
        foreach($info as $v)
        {
            $ret[] = unserialize($v);
        }
        return $ret;
    }

    function getUser($client_id)
    {
        $ret = $this->redis->get(self::$prefix.'client_'.$client_id);
        $info = unserialize($ret);
        return $info;
    }
	
	 /**
     * �����ʷ�����¼
     */
    function addRoomsHistory($room_id, $uid, $msg) {
        $info = $this->getUser($uid);

        $log['user'] = $info;
        $log['msg'] = $msg;
        $log['time'] = time();

        $this->redis->lPush("im_room_chat_".$room_id, json_encode($log));
    }
	
		 /**
     * �����ʷ�����¼
     */
    function addHistory($uid, $msg) {
        $info = $this->getUser($uid);

        $log['user'] = $info;
        $log['msg'] = $msg;
        $log['time'] = time();

        $this->redis->lPush("im_room_chat", json_encode($log));
    }
	
	 /**
     * ��ȡ��ʷ�����¼()
     */
	function getRoomsHistory($room_id, $offset = 0, $num = 100)
    {
        return $this->redis->lRange("im_room_chat_".$room_id, $offset, $num);
    }
}