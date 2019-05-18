<?php

namespace nulastudio\SSR;

use nulastudio\SSR\Util;

class SS
{
    public $host;     // 服务器
    public $port;     // 端口
    public $method;   // 加密方法
    public $password; // 密码

    public function __construct($host, $port, $method, $password)
    {
        $this->host     = $host;
        $this->port     = (int) $port;
        $this->method   = $method;
        $this->password = $password;
    }

    public static function parseFromLink($link)
    {
        if (substr($link, 0, 5) !== 'ss://') {
            throw new \Exception('invalid ss link.');
        }
        $info                    = Util::urlSafeBase64Decode(substr($link, 5));
        list($auth, $server)     = explode('@', $info);
        list($method, $password) = explode(':', $auth);
        list($host, $port)       = explode(':', $server);

        $arr = array(
            'host'     => $host,
            'port'     => $port,
            'method'   => $method,
            'password' => $password,
        );

        return static::parseFromArray($arr);
    }

    public static function parseFromArray($arr)
    {
        $host     = isset($arr['host']) ? $arr['host'] : '';
        $port     = isset($arr['port']) ? (int) $arr['port'] : 1080;
        $method   = isset($arr['method']) ? $arr['method'] : 'AES-256-CFB';
        $password = isset($arr['password']) ? $arr['password'] : '';
        $ss       = new static($host, $port, $method, $password);
        return $ss;
    }

    public function __toString()
    {
        $str = "{$this->method}:{$this->password}@{$this->host}:{$this->port}";
        return 'ss://' . Util::urlSafeBase64Encode($str);
    }
}
