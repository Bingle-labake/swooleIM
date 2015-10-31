<?php
namespace WebIM;
use Swoole;

class Server extends \Swoole\Protocol\WebSocket {
    /**
     * @var Store\File;
     */
    protected $store;
    protected $rooms;

    const MESSAGE_MAX_LEN     = 1024; //单条消息不得超过1K
    const WORKER_HISTORY_ID   = 0;
    const ONLY_ROOM_CHAT      = 1;    //仅仅支持群聊

    function __construct($config = array()) {		
        //检测日志目录是否存在
        $log_dir = dirname($config['webim']['log_file']);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        if (!empty($config['webim']['log_file'])) {
            $logger = new Swoole\Log\FileLog($config['webim']['log_file']);
        }else{
            $logger = new Swoole\Log\EchoLog;
        }
        $this->setLogger($logger);   //Logger
        /**
         * 使用文件或redis存储聊天信息         
        $this->setStore(new \WebIM\Store\File($config['webim']['data_dir']));
		*/
		
		/**
        * 使用redis存储聊天信息
        */
        $this->setStore(new \WebIM\Store\Redis());
        $this->setRooms(new \WebIM\Store\Rooms());	
		
        $this->origin = $config['server']['origin'];
		
        parent::__construct($config);
    }
	
	/*
    function __construct($config = array()) {
        parent::__construct($config);         
    }
    */
	
    function setStore($store) {
        $this->store = $store;
    }
    
    function setRooms($rooms) {
    	$this->rooms = $rooms;
    }
	
	function curl($url, $data = '', $method = "post") {
	    $poststr = '';
	    $i = 0;
	    $ch = curl_init ();
	
	    if ($method == "post") {
		    if (is_array ( $data )) {
			    foreach ( $data as $dkey => $dval ) {
				    $poststr .= $dkey . '=' . $dval;
				    if ($i < count ( $data ))
					    $poststr .= '&';
				    $i ++;
			    }
		    } else {
			    $poststr = $data;
		    }
		    curl_setopt ( $ch, CURLOPT_URL, $url );
		    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		    curl_setopt ( $ch, CURLOPT_POST, true );
		    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $poststr );
		    curl_setopt ( $ch, CURLOPT_TIMEOUT, 6 );
		    curl_setopt ( $ch, CURLOPT_USERAGENT, "Opera/9.25" );
	    } else {
		    curl_setopt ( $ch, CURLOPT_URL, $url );
		    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	    }
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    $result = curl_exec ( $ch );
	    curl_close ( $ch );
	    return $result;
    }



    /**
     * 登录(加入房间)
     * @param $client_id
     * @param $msg
     */
    function cmd_joinRooms($client_id, $msg) {
        $info['name']       = $msg['name'];
		$info['uid']        = $msg['uid'];
        $info['rid']        = $msg['rid'];
		$info['client_id']  = $client_id;
		
		if(isset($msg['rid']) && $msg['rid'] > 0) {
			$room_id = $msg['rid'];
			
			//回复给登录用户
            $resMsg = array(
                           'cmd' => 'joinRooms',
                           'fd' => $client_id,
						   'uid' => $msg['uid'],
                           'name' => $msg['name'],
                           'rid' => $msg['rid'],
            );
        
            $this->store->login($client_id, $room_id, $resMsg);  
            $exist_client = $this->rooms->existClientForRooms($client_id, $room_id);
            $flag = true;
            if(empty($exist_client)) {
            	$flag = $this->rooms->addRooms($msg['uid'], $client_id, $room_id);
            }			
			if($flag) {
			    $this->sendJson($client_id, $resMsg);
				echo "ask->send joinRooms";
				
				//广播给其它在线用户
                $resMsg['cmd'] = 'newUser';
                //将上线消息发送给所有人
                $this->broadcastRoomsJson($client_id, $room_id, $resMsg);
                echo "broadcast->send newUser";
                
                //用户登录消息
                $loginMsg = array(
                             'cmd' => 'fromMsg',
                             'from' => 0,
                		     'fd' => $client_id,
                             'data' => $msg['name'] . "上线鸟。。",
                             );
			
			    $this->broadcastRoomsJson($client_id, $room_id, $loginMsg);
			    echo "broadcast->send fromMsg";
			}else {
				$this->sendErrorMessage($client_id, 102, '加入房间失败...');
                return;
			}            
		}else {
			//回复给登录用户
            $this->sendErrorMessage($client_id, 102, '非房间用户无法加入');
            return;
		}        
    }
	
	
    /**
     * 下线时(离开房间)，通知房间所有人
     */
    function onClose($serv, $client_id, $from_id) {		
        $userInfo = $this->store->getUser($client_id);		
        if (!$userInfo) {
            return;
        }
        $resMsg = array(
            'cmd'  => 'offline',
            'fd'   => $client_id,
            'from' => 0,
            'rim'  => $userInfo['rid'],
            'data' => $userInfo['name'] . "下线了。。",
        );
  
        //将下线消息发送给所有人
        $this->log("onOffline: " . $client_id);
        $room_id = isset($userInfo['rid'])?$userInfo['rid']:0;
        
        echo PHP_EOL."onClose: room_id:".$room_id.", client_id:".$client_id;
        if($room_id > 0) {			
			$flag = $this->rooms->leaveRooms($client_id, $room_id);
			echo PHP_EOL."leaveRooms: room_id:".$room_id.", client_id:".$client_id;
			
			$this->store->logout($client_id, $room_id);		
            $this->broadcastRoomsJson($client_id, $room_id, $resMsg);
            echo PHP_EOL."broadcastRooms: room_id:".$room_id.", client_id:".$client_id;
		}else {
			$this->store->logout($client_id);		
            $this->broadcastJson($client_id, $resMsg);
		}
		
        parent::onClose($serv, $client_id, $from_id);
    }

    /**
     * 获取房间在线列表
     */
    function cmd_getRoomsOnline($client_id, $msg) {
    	$resMsg = array(
    			'cmd' => 'getRoomsOnline',
    	);
		$room_id = $msg['rid'];
		
		echo "getRoomsOnline: room_id:".$room_id;
		if($room_id > 0) {
			$room_clients = $this->store->getOnlineUsers($room_id);
			
    	    $info = $this->store->getUsers($room_clients);
    	    $resMsg['users'] = $room_clients;
    	    $resMsg['list'] = $info;
			
			$this->sendJson($client_id, $resMsg);
		}else {
			$this->sendErrorMessage($client_id, 102, '非房间用户，获取失败...');
            return;
		}     	
    }
	
	
    //任务
    function onTask($serv, $task_id, $from_id, $data) {
        $req = unserialize($data);
        if ($req) {
            switch($req['cmd']) {
                case 'getRoomsHistory':
                    $history = $this->store->getRoomsHistory($req['rid']);
                    $history =  array_reverse($history);
                    $this->sendJson($req['fd'], array('cmd'=> 'getRoomsHistory', 'fd'=>$req['fd'], 'history' => $history));
                    break;
					
                case 'addRoomsHistory':
                    $this->store->addRoomsHistory($req['rid'], $req['fd'], $req['msg']);
                    break;
					
				case 'addHistory':
                    $this->store->addHistory($req['fd'], $req['msg']);
                    break;
				case 'presentGift'://赠送礼物
					$client_id = $req['fd'];
					$room_id   = $req['rid'];
					$resMsg    = $req['msg'];
					
					$url = "http://demo.api.saiku.com.cn/eshop/present";
					$data = array('uid'=>$resMsg['uid'], 
								  'fuid'=>$resMsg['f_uid'], 
								  'rid'=>$room_id, 
								  'ides'=>$resMsg['f_gift'], 
								  'num'=>$resMsg['f_num']);
					$ret = @$this->curl($url, $data);
					echo "presentGift......uid[".$resMsg['uid']."] send fuid[".$resMsg['f_uid']."]->".$resMsg['f_gift']."]".$resMsg['f_num'];
					if($ret) {						
						$ret = json_decode($ret, true);						
						if($ret['result']) {
						    $resMsg['data'] = $ret['data'];
							$this->broadcastRoomsJson($client_id, $room_id, $resMsg, false);
							
							//保存聊天记录
							$user = $ret['data']['user'];
							$fuser = $ret['data']['fuser'];
							$gift  = $ret['data']['gift'];
							
							$gift_html = '';
							foreach($gift as $ide=>$v) {
								$item = '';
								for($i=0; $i<$v['num'];$i++) {
									$item .= '<i class="'.$v['class'].'" alt="'.$v['name'].'"></i>';
								}
								$item .= " ".$v['num']."个";
								if(empty($gift_html)) {
									$gift_html = $item;
								}else {
									$gift_html .= "，".$item;
								}
							}
							$gift_html = "给 <a href='http://demo.q.saiku.com.cn/u/".$fuser['uid']."'>".$fuser['username']."</a> 送了". $gift_html. "。";
							$msg = $req['msg'];
							$msg['data'] = $gift_html;
							$this->getSwooleServer()->task(serialize(array(
									'cmd' => 'addRoomsHistory',
									'msg' => $msg,
									'fd'  => $client_id,
									'rid' => $room_id,
							)), self::WORKER_HISTORY_ID);
						}else {
							$resMsg['cmd'] = 'presentError';
							$resMsg['data'] = $ret['error'];
							$this->sendJson($client_id, $resMsg);
						}
					}					
					break;
                default:
                    break;
            }
        }
    }
    
    //定时器
    function onTimer($serv, $interval) {
    	   	
    }

    function onFinish($serv, $task_id, $data) {

    }

    /**
     * 获取房间历史聊天记录
     */
    function cmd_getRoomsHistory($client_id, $msg) {
        $task['fd'] = $client_id;
		$task['rid'] = $msg['rid'];
        $task['cmd'] = 'getRoomsHistory';
        $task['offset'] = '0,100';
        
        echo "getRoomsHistory: room_id:".$task['rid'];
        
        //在task worker中会直接发送给客户端
        $this->getSwooleServer()->task(serialize($task), self::WORKER_HISTORY_ID);
    }

    /**
     * 发送信息请求
     */
    function cmd_message($client_id, $msg) {
        $resMsg = $msg;
        $resMsg['cmd'] = 'fromMsg';
        if (strlen($msg['data']) > self::MESSAGE_MAX_LEN) {
            $this->sendErrorMessage($client_id, 102, 'message max length is '.self::MESSAGE_MAX_LEN);
            return;
        }

        //表示群发
        if ($msg['rid'] > 0) {
        	//广播组      	
            $this->broadcastRoomsJson($client_id, $msg['rid'], $resMsg);
            
            //保存聊天记录
            $this->getSwooleServer()->task(serialize(array(
                'cmd' => 'addRoomsHistory',
                'msg' => $msg,
                'fd'  => $client_id,
				'rid' => $msg['rid'],
            )), self::WORKER_HISTORY_ID);
        }
        
        //表示私聊
        elseif ($msg['room_id'] == 0) {
        	if(self::ONLY_ROOM_CHAT == 1) {
        		$resMsg['msg'] = 'send failed! only group chat.';
        		$this->sendJson($msg['from'], $resMsg);
        	}else {
        		$this->sendJson($msg['to'], $resMsg);
        		$this->store->addHistory($client_id, $msg['data']);
        		$this->sendJson($msg['from'], $resMsg);
        	}            
        }
    }
    
    /**
     * 发送[系统广播]信息请求
     */
    /*
    function cmd_broadcast($client_id, $msg) {
    	$resMsg = $msg;
    	$resMsg['cmd'] = 'fromMsg';
    
    	if (strlen($msg['data']) > self::MESSAGE_MAX_LEN) {
    		$this->sendErrorMessage($client_id, 102, 'message max length is '.self::MESSAGE_MAX_LEN);
    		return;
    	}
    
    	$this->broadcastJson($client_id, $resMsg);
    	$this->getSwooleServer()->task(serialize(array(
    				'cmd' => 'addHistory',
    				'msg' => $msg,
    				'fd'  => $client_id,
    	)), self::WORKER_HISTORY_ID);
    }
    */
	
	 /**
     * 发送礼物、道具信息请求[特殊字符串解析]
     */
    function cmd_presentGift($client_id, $msg) {
        $resMsg = $msg;
        $resMsg['cmd'] = 'fromPresentGift';

        if (strlen($msg['data']) > self::MESSAGE_MAX_LEN) {
            $this->sendErrorMessage($client_id, 102, 'message max length is '.self::MESSAGE_MAX_LEN);
            return;
        }

        $room_id = $msg['rid'];
        if ($room_id >= 0) {
        	/*
        	 * msg.from = client_id;     
               msg.to = 0;
               msg.uid = uid;
               msg.rid = rid;
               msg.data = "礼物赠送";
               msg.f_gift = gifts;
               msg.f_num  = num;
               msg.f_uid  = fuid;
        	 */  			
            $this->getSwooleServer()->task(serialize(array(
                'cmd' => 'presentGift',
                'msg' => $resMsg,
                'fd'  => $client_id,
				'rid' => $msg['rid'],
            )), self::WORKER_HISTORY_ID);
        }else {
			$this->sendErrorMessage($client_id, 102, '非房间会员无法赠送...');
            return;
		}
    }
	
	/*
	*客户端握手
	*
	*/
	function cmd_handshake($client_id, $msg) {
		$msg['cmd']       = 'handshake';
		$msg['msg']       = 'ack_ok';
		$msg['client_id'] = $client_id;
        $this->sendJson($client_id, $msg);
	}

    /**
     * 接收到消息时
     * @see WSProtocol::onMessage()
     */
    function onMessage($client_id, $ws) {
        $this->log("onMessage: " . $ws['message']);
        echo $ws['message'];
        
        $msg = json_decode($ws['message'], true);
        if (empty($msg['cmd'])) {
            $this->sendErrorMessage($client_id, 101, "invalid command");
            return;
        }
        $func = 'cmd_'.$msg['cmd'];
        $this->$func($client_id, $msg);
    }

    /**
     * 发送错误信息
    * @param $client_id
    * @param $code
    * @param $msg
     */
    function sendErrorMessage($client_id, $code, $msg)  {
        $this->sendJson($client_id, array('cmd' => 'error', 'code' => $code, 'msg' => $msg));
    }

    /**
     * 发送JSON数据
     * @param $client_id
     * @param $array
     */
    function sendJson($client_id, $array) {
        $msg = json_encode($array);
        $this->send($client_id, $msg);
    }

    /**
     * 广播JSON数据
     * @param $client_id
     * @param $array
     */
    function broadcastJson($client_id, $array) {
        $msg = json_encode($array);
        $this->broadcast($client_id, $msg);
    }
    
    /**
     * 房间广播JSON数据
     * @param $client_id
     * @param $array
     */
    function broadcastRoomsJson($client_id, $room_id, $array, $self_exc = true) {
    	$msg = json_encode($array);
    	$this->broadcastRooms($client_id, $room_id, $msg, $self_exc);
    }

    /**
     * 广播JSON数据
     * @param $client_id
     * @param $array
     */
    function broadcast($client_id, $msg) {
        if (extension_loaded('swoole')) {
            $sw_serv = $this->getSwooleServer();
            $start_fd = 0;
            while(true) {
                $conn_list = $sw_serv->connection_list($start_fd, 10);
                if($conn_list === false) {
                    break;
                }
                $start_fd = end($conn_list);
                foreach($conn_list as $fd) {
                    if($fd === $client_id) continue;
                    $this->send($fd, $msg);
                }
            }
        }
        else {
            foreach ($this->connections as $fd => $info) {
                if ($client_id != $fd) {
                    $this->send($fd, $msg);
                }
            }
        }
    }
    
    /**
     * 房间广播JSON数据
     * @param $client_id
     * @param $array
     */
    function broadcastRooms($client_id, $room_id, $msg, $self_exc = true) {
    	$start = 0;
		$end = 10;
    	while(true) {
			$conn_list = $this->rooms->getRoomClients($room_id, $start, $end); 
			  			
    		if(empty($conn_list) || $conn_list === false) {
    			break;
    		}
    		$start = $end;
			$end = $start + 10;
    		foreach($conn_list as $fd=>$uid) {
    			if($self_exc && $fd === $client_id) {
    				continue;
    			}	
    			
    			$msg = json_decode($msg, true);
    			$msg['fd'] = $fd;
    			$msg = json_encode($msg);
    			
    			$this->send($fd, $msg);  
    			echo PHP_EOL."broadcast:".$uid."[".$fd."]";  			
    		}
    	}
    }
}
