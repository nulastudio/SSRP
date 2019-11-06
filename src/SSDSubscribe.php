<?php

namespace nulastudio\SSR;

use nulastudio\SSR\SS;
use nulastudio\SSR\SSD;
use nulastudio\SSR\SSR;
use nulastudio\SSR\Util;

/**
 * SSD协议解析
 *
 * @see https://github.com/TheCGDF/SSD-Windows/wiki/HTTP%E8%AE%A2%E9%98%85%E5%8D%8F%E5%AE%9A
 * @see https://github.com/TheCGDF/SSD-Windows/wiki/HTTP%E8%AE%A2%E9%98%85%E5%8D%8F%E5%AE%9A%C2%B7%E8%AE%BE%E8%AE%A1%E4%BA%8B%E6%95%85
 */
class SSDSubscribe implements \Countable, \Iterator, \ArrayAccess
{
    private $position      = 0;
    protected $servers     = [];
    public $airport        = 'empty ssd subscribe';
    public $port           = 1080;
    public $encryption     = 'aes-256-cfb';
    public $password       = 'none';
    public $plugin         = null;
    public $plugin_options = null;
    public $traffic_used   = null;
    public $traffic_total  = null;
    public $expiry         = null;
    public $url            = null;

    public function __construct($name = null, $ssd = null)
    {
        if ($name) {
            $this->airport = $name;
        }
        if (!is_array($ssd)) {
            $ssd = [$ssd];
        }
        foreach ($ssd as $_ssd) {
            $this->addSSD($_ssd);
        }
    }

    public function addSSD($server)
    {
        if ($server instanceof SSD) {
            $this->servers[] = $server;
        } else if ($server instanceof SSR) {
            $this->servers[] = SSD::parseFromSSR($server);
        } else if ($server instanceof SS) {
            $this->servers[] = SSD::parseFromSS($server);
        } else if (is_string($server)) {
            if (substr($server, 0, 5) === 'ss://') {
                $this->addSSD(SSD::parseFromSSLink($server));
            } else if (substr($server, 0, 6) === 'ssr://') {
                $this->addSSD(SSD::parseFromSSRLink($server));
            }
        }
    }

    public static function parseFromSubscribe($pack)
    {
        $pack = preg_replace('#[^A-Za-z0-9\+\/\=\-\_\:]#', '', $pack);
        if (substr($pack, 0, 6) !== 'ssd://') {
            throw new \Exception('invalid ssd subscribe');
        }
        $pack    = str_replace('ssd://', '', $pack);
        $airport = Util::urlSafeBase64Decode($pack);
        $airport = json_decode($airport, true);

        // servers
        $servers = [];
        foreach ($airport['servers'] ?? [] as $server) {
            $servers[] = SSD::parseFromArray($server);
        }

        $subscribe = new static('unname', $servers);

        // 机场配置赋值
        $airportConfig = ['airport', 'port', 'encryption', 'password', 'plugin', 'plugin_options', 'traffic_used', 'traffic_total', 'expiry', 'url'];
        foreach ($airportConfig as $config) {
            if (!empty($airport[$config])) {
                $subscribe->$config = $airport[$config];
            }
        }

        return $subscribe;
    }

    public function __toString()
    {
        $airport = [];

        // 机场配置赋值
        $airportConfig = ['airport', 'port', 'encryption', 'password', 'plugin', 'plugin_options', 'traffic_used', 'traffic_total', 'expiry', 'url', 'servers'];
        foreach ($airportConfig as $config) {
            if (!empty($this->$config)) {
                $airport[$config] = $this->$config;
            }
        }

        // servers
        array_walk($airport['servers'], function (&$ssd) {
            $ssd = $ssd->toArray();
        });

        return 'ssd://' . base64_encode(json_encode($airport));
    }

    public function count()
    {
        return count($this->servers);
    }

    public function current()
    {
        return $this->servers[$this->position];
    }
    public function key()
    {
        return $this->position;
    }
    public function next()
    {
        ++$this->position;
    }
    public function rewind()
    {
        $this->position = 0;
    }
    public function valid()
    {
        return isset($this->servers[$this->position]);
    }

    public function offsetExists($offset)
    {
        return isset($this->servers[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->servers[$offset]) ? $this->servers[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->servers[] = $value;
        } else {
            $this->servers[$offset] = $value;
        }
    }
    public function offsetUnset($offset)
    {
        unset($this->servers[$offset]);
    }
}
