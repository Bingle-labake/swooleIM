PHPWebIM
========

使用PHP+Swoole实现的网页即时聊天工具，在线体验地址：[http://webim.swoole.com/](http://webim.swoole.com/)

* 全异步非阻塞Server，可以同时支持数百万TCP连接在线
* 基于websocket+flash_websocket支持所有浏览器/客户端/移动端
* 支持单聊/群聊/组聊等功能
* 支持永久保存聊天记录
* 基于Server PUSH的即时内容更新，登录/登出/状态变更/消息等会内容即时更新
* 支持发送连接/图片/语音/视频/文件（开发中）
* 支持Web端直接管理所有在线用户和群组（开发中）

安装
----
swoole扩展
```shell
pecl install swoole
```

swoole框架
```shell
composer install
```

运行
----
将client目录配置到Nginx/Apache的虚拟主机目录中，使client/index.html可访问。
修改client/config.js中，IP和端口为对应的配置。
```shell
php webim_server.php
```

详细部署说明
----

1.安装composer(php依赖包工具)

```shell
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

注意：如果未将php解释器程序设置为环境变量PATH中，需要设置。因为composer文件第一行为#!/usr/bin/env php，并不能修改。
更加详细的对composer说明：http://blog.csdn.net/zzulp/article/details/18981029

2.composer install

切换到PHPWebIM项目目录，执行指令composer install，如很慢则

```shell
composer install --prefer-dist
```

3.Ningx/Apache配置（这里未使用swoole_framework提供的Web AppServer）

nginx

```shell
server
{
    listen       80;
    server_name  im.swoole.com;
    index index.shtml index.html index.htm index.php;
    root  /path/to/PHPWebIM/client;
    location ~ .*\.(php|php5)?$
    {
	    fastcgi_pass  127.0.0.1:9000;
	    fastcgi_index index.php;
	    include fastcgi.conf;
    }
    access_log  /Library/WebServer/nginx/logs/im.swoole.com  access;
}
```

apache

```shell
<VirtualHost *:80>
    DocumentRoot "path/to/PHPWebIM/client"
    ServerName im.swoole.com
    AddType application/x-httpd-php .php
    <Directory />
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

4.修改配置PHPWebIM/config.php

```php
$config['server'] = array(
    'host' => '19.0.3.245',
    'port' => '9503',
);
```

其中server项为WebIM服务器即WebSocket服务器的IP与端口，其他选择项根据具体情况修改

5.修改配置PHPWebIM/client/config.js

```js
var webim = {
    'server' : 'ws://19.0.3.245:9503'
}
```

server对应4中的配置，ws://IP:端口

6.启动WebSocket服务器

```shell
php PHPWebIM/webim_server.php
```

7.绑定host与访问聊天窗口

```shell
vi /etc/hosts
```

增加

```shell
127.0.0.1 im.swoole.com
```

用浏览器打开：http://im.swoole.com

快速了解项目架构
----

1.目录结构

```
+ PHPWebIM
  |- webim_server.php //WebSocket协议服务器
  |- config.php // swoole运行配置
  |+ swoole.ini // WebSocket协议实现配置
  |+ client
    |+ static
    |- config.js // WebSocket client配置
    |- index.html // 登录界面
    |- main.html // 聊天室主界面
  |+ data // 运行数据
  |+ log // swoole日志及WebIM日志
  |+ src // WebIM 类文件储存目录
    |+ Store
      |- File.php // 默认用内存tmpfs文件系统(linux /dev/shm)存放天着数据，如果不是linux请手动修改$shm_dir
      |- Redis.php // 将聊天数据存放到Redis
    |- Server.php // 继承实现WebSocket的类，完成某些业务功能
  |+ vendor // 依赖包目录
```

2.Socket Server与Socket Client通信数据格式

如：登录

Client发送数据

```js
{"cmd":"login","name":"xdy","avatar":"http://tp3.sinaimg.cn/1586005914/50/5649388281/1"}
```

Server响应登录

```js
{"cmd":"login", "fd": "31", "name":"xdy","avatar":"http://tp3.sinaimg.cn/1586005914/50/5649388281/1"}
```

可以看到cmd属性，client与server发送时数据都有指定，主要是用于client或者server的回调处理函数。

3.需要理清的几种协议或者服务的关系

http协议：超文本传输协议。单工通信，等着客户端请求之后响应。

WebSocket协议：是HTML5一种新的协议，它是实现了浏览器与服务器全双工通信。服务器端口与客户端都可以推拉数据。

Web服务器：此项目中可以用基于Swoole的App Server充当Web服务器，也可以用传统的nginx/apache作为web服务器

Socket服务器：此项目中浏览器的WebSocket客户端连接的服务器，swoole_framework中有实现WebSocket协议PHP版本的服务器。

WebSocket Client：实现html5的浏览器都支持WebSocket对象，如不支持此项目中有提供flash版本的实现。








