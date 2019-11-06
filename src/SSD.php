<?php

namespace nulastudio\SSR;

use nulastudio\SSR\SS;
use nulastudio\SSR\SSR;

/**
 * SSD协议解析
 *
 * @see https://github.com/TheCGDF/SSD-Windows/wiki/HTTP%E8%AE%A2%E9%98%85%E5%8D%8F%E5%AE%9A
 * @see https://github.com/TheCGDF/SSD-Windows/wiki/HTTP%E8%AE%A2%E9%98%85%E5%8D%8F%E5%AE%9A%C2%B7%E8%AE%BE%E8%AE%A1%E4%BA%8B%E6%95%85
 */
class SSD
{
    public $id;             // 服务器ID，int，可选
    public $server;         // 服务器
    public $port;           // 端口，扩展
    public $encryption;     // 加密，扩展
    public $password;       // 密码，扩展
    public $plugin;         // 插件，扩展
    public $plugin_options; // 插件参数，扩展
    public $remarks;        // 备注，可选
    public $ratio;          // 服务器流量费率，float，可选

    public function __construct($server, $port = 0, $encryption = null, $password = null, $plugin = null, $plugin_options = null, $remarks = '', $id = 0, $ratio = 0)
    {
        $this->server         = $server;
        $this->port           = (int) $port;
        $this->encryption     = $encryption;
        $this->password       = $password;
        $this->plugin         = $plugin;
        $this->plugin_options = $plugin_options;
        $this->remarks        = $remarks;
        $this->id             = (int) $id;
        $this->ratio          = (float) $ratio;
    }

    public static function parseFromLink($link)
    {
        // NOTE: ssd has no own link, `ssd://` is for ssd subscribe
        throw new \Exception('ssd has no own link, `ssd://` is for ssd subscribe');
    }

    public static function parseFromSSLink($link)
    {
        return static::parseFromSS(SS::parseFromLink($link));
    }

    public static function parseFromSSRLink($link)
    {
        return static::parseFromSSR(SSR::parseFromLink($link));
    }

    public static function parseFromSS(SS $ss)
    {
        return static::parseFromArray([
            'server'     => $ss->host,
            'port'       => $ss->port,
            'encryption' => $ss->method,
            'password'   => $ss->password,
        ]);
    }

    public static function parseFromSSR(SSR $ssr)
    {
        return static::parseFromArray([
            'server'   => $ssr->host,
            'port'     => $ssr->port,
            'method'   => $ssr->method,
            'password' => $ssr->password,
        ]);
    }

    public static function parseFromArray($arr)
    {
        // 缺省参数从订阅（机场）配置里继承
        $server         = isset($arr['server']) ? $arr['server'] : '';
        $port           = isset($arr['port']) ? $arr['port'] : null;
        $encryption     = isset($arr['encryption']) ? $arr['encryption'] : null;
        $password       = isset($arr['password']) ? $arr['password'] : null;
        $plugin         = isset($arr['plugin']) ? $arr['plugin'] : null;
        $plugin_options = isset($arr['plugin_options']) ? $arr['plugin_options'] : null;
        $remarks        = isset($arr['remarks']) ? $arr['remarks'] : '';
        $id             = isset($arr['id']) ? $arr['id'] : null;
        $ratio          = isset($arr['ratio']) ? $arr['ratio'] : null;
        $ssd            = new static($server, $port, $encryption, $password, $plugin, $plugin_options, $remarks, $id, $ratio);
        return $ssd;
    }

    public function toArray()
    {
        $ssd = [
            'server' => $this->server,
        ];
        if ($this->port) {
            $ssd['port'] = $this->port;
        }
        if ($this->encryption) {
            $ssd['encryption'] = $this->encryption;
        }
        if ($this->password) {
            $ssd['password'] = $this->password;
        }
        if ($this->plugin) {
            $ssd['plugin'] = $this->plugin;
        }
        if ($this->plugin_options) {
            $ssd['plugin_options'] = $this->plugin_options;
        }
        if ($this->remarks) {
            $ssd['remarks'] = $this->remarks;
        }
        if ($this->id) {
            $ssd['id'] = $this->id;
        }
        if ($this->ratio) {
            $ssd['ratio'] = $this->ratio;
        }

        return $ssd;
    }

    public function __toString()
    {
        // NOTE: no need
        return json_encode($this->toArray());
    }
}
