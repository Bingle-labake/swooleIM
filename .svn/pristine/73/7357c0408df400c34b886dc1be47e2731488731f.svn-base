<?php
namespace WebIM\Store;

class Rooms
{
    /**
     * @var \rooms
     */
    protected $rooms;

    static $prefix = "im_room_";

    function __construct($host = '127.0.0.1', $port = 6379, $timeout = 0.0)
    {
        $redis = new \redis;
        $redis->connect($host, $port, $timeout);
        $this->redis = $redis;
    }
 
    //连接加入房间
    //zAdd(key, score, member)：
    function addRooms($uid, $client_id, $room_id) {
    	return $this->redis->zAdd(self::$prefix.$room_id, $uid, $client_id);
    }
    
    //连接离开房间
    //zRem(key, member) ：删除名称为key的zset中的元素member
    function leaveRooms($client_id, $room_id) {
    	return $this->redis->zDelete(self::$prefix.$room_id, $client_id);
    }
    
    //获取房间的所有连接
    //zRange(key, start, end,withscores)：返回名称为key的zset（元素已按score从小到大排序）中的index从start到end的所有元素
    function getRoomClients($room_id, $start = 0, $end = -1) {
    	return $this->redis->zRange(self::$prefix.$room_id, $start, $end, true);
    }
    
    //获取房间大小
    function getRoomSize($room_id) {
    	return $this->redis->zSize(self::$prefix.$room_id);
    }
    
    //判断元素是否存在
    //返回名称为key的zset中元素member的score
    function existClientForRooms($client_id, $room_id) {
    	return $this->redis->zScore(self::$prefix.$room_id, $client_id);
    }
}