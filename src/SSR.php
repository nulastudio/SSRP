<?php

namespace nulastudio\SSR;

use nulastudio\SSR\SS;
use nulastudio\SSR\Util;

class SSR
{
    public $host;       // 服务器
    public $port;       // 端口
    public $protocol;   // 协议
    public $method;     // 加密方法
    public $obfs;       // 混淆
    public $password;   // 密码
    public $obfsParam;  // 混淆参数
    public $protoParam; // 协议参数
    public $remarks;    // 备注
    public $group;      // 组
    public $uot;        // udp over tcp
    public $udpPort;    // udp端口

    public function __construct($host, $port, $protocol, $method, $obfs, $password, $obfsParam, $protoParam, $remarks = '', $group = '', $uot = false, $udpPort = 1081)
    {
        $this->host       = $host;
        $this->port       = (int) $port;
        $this->protocol   = $protocol;
        $this->method     = $method;
        $this->obfs       = $obfs;
        $this->password   = $password;
        $this->obfsParam  = $obfsParam;
        $this->protoParam = $protoParam;
        $this->remarks    = $remarks;
        $this->group      = $group;
        $this->uot        = (bool) $uot;
        $this->udpPort    = (int) $udpPort;
    }

    public static function parseFromLink($link)
    {
        if (substr($link, 0, 6) !== 'ssr://') {
            throw new \Exception('invalid ssr link.');
        }
        $info                = Util::urlSafeBase64Decode(substr($link, 6));
        list($info, $params) = explode('/?', $info);

        $mainInfo   = explode(':', $info);
        $base64pass = array_pop($mainInfo);
        $obfs       = array_pop($mainInfo);
        $method     = array_pop($mainInfo);
        $protocol   = array_pop($mainInfo);
        $port       = array_pop($mainInfo);
        $host       = implode(':', $mainInfo);

        $protocol = str_replace('_compatible', '', $protocol);
        $obfs     = str_replace('_compatible', '', $obfs);

        $arr = array(
            'host'     => $host,
            'port'     => (int) $port,
            'protocol' => $protocol ?: 'origin',
            'method'   => $method,
            'obfs'     => $obfs ?: 'plain',
            'password' => Util::urlSafeBase64Decode($base64pass),
        );
        foreach (explode('&', $params) as $kv) {
            // 兼容非urlsafe base64
            $segments = explode('=', $kv);
            $key      = array_shift($segments);
            $value    = implode('=', $segments);
            // list($key, $value) = explode('=', $kv);
            $arr[$key] = Util::urlSafeBase64Decode($value);
        }

        return static::parseFromArray($arr);
    }

    public static function parseFromSSLink($link)
    {
        return static::parseFromSS(SS::parseFromLink($link));
    }

    public static function parseFromSS(SS $ss)
    {
        return static::parseFromArray([
            'host'     => $ss->host,
            'port'     => $ss->port,
            'method'   => $ss->method,
            'password' => $ss->password,
            'protocol' => 'origin',
            'obfs'     => 'plain',
        ]);
    }

    public static function parseFromArray($arr)
    {
        $host       = isset($arr['host']) ? $arr['host'] : '';
        $port       = isset($arr['port']) ? $arr['port'] : 1080;
        $protocol   = isset($arr['protocol']) ? $arr['protocol'] : '';
        $method     = isset($arr['method']) ? $arr['method'] : '';
        $obfs       = isset($arr['obfs']) ? $arr['obfs'] : '';
        $password   = isset($arr['password']) ? $arr['password'] : '';
        $obfsParam  = isset($arr['obfsparam']) ? $arr['obfsparam'] : '';
        $protoParam = isset($arr['protoparam']) ? $arr['protoparam'] : '';
        $remarks    = isset($arr['remarks']) ? $arr['remarks'] : '';
        $group      = isset($arr['group']) ? $arr['group'] : '';
        $udpPort    = isset($arr['udpport']) ? $arr['udpport'] : '';
        $uot        = isset($arr['uot']) ? $arr['uot'] : '';
        $ssr        = new static($host, $port, $protocol, $method, $obfs, $password, $obfsParam, $protoParam, $remarks, $group, $udpPort, $uot);
        return $ssr;
    }

    public function __toString()
    {
        $base64pass = Util::urlSafeBase64Encode($this->password);
        $path       = "{$this->host}:{$this->port}:{$this->protocol}:{$this->method}:{$this->obfs}:{$base64pass}";
        $query      = 'obfsparam=' . Util::urlSafeBase64Encode($this->obfsParam ?: '');
        if (!empty($this->protoParam)) {
            $query .= '&protoparam=' . Util::urlSafeBase64Encode($this->protoParam);
        }
        if (!empty($this->remarks)) {
            $query .= '&remarks=' . Util::urlSafeBase64Encode($this->remarks);
        }
        if (!empty($this->group)) {
            $query .= '&group=' . Util::urlSafeBase64Encode($this->group);
        }
        if ($this->uot) {
            $query .= '&uot=1';
        }
        if ($this->udpPort > 0) {
            $query .= '&udpport=' . $this->udpPort;
        }
        return 'ssr://' . Util::urlSafeBase64Encode("{$path}/?{$query}");
    }
}
