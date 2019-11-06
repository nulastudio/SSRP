<?php

namespace nulastudio\SSR;

use nulastudio\SSR\SS;
use nulastudio\SSR\SSR;
use nulastudio\SSR\Util;

class SSRSubscribe implements \Countable, \Iterator, \ArrayAccess
{
    private $position = 0;
    protected $SSRs   = [];
    public $name      = 'empty subscribe';

    public function __construct($name = null, $ssr = null)
    {
        if ($name) {
            $this->name = $name;
        }
        if (!is_array($ssr)) {
            $ssr = [$ssr];
        }
        foreach ($ssr as $_ssr) {
            $this->addSSR($_ssr);
        }
    }

    public function addSSR($ssr)
    {
        if ($ssr instanceof SSR) {
            $this->SSRs[] = $ssr;
        } else if ($ssr instanceof SS) {
            $this->SSRs[] = SSR::parseFromSS($ssr);
        } else if (is_string($ssr)) {
            if (substr($ssr, 0, 5) === 'ss://') {
                $this->addSSR(SS::parseFromLink($ssr));
            } else if (substr($ssr, 0, 6) === 'ssr://') {
                $this->addSSR(SSR::parseFromLink($ssr));
            }
        }
    }

    public static function parseFromSubscribe($pack)
    {
        $pack      = Util::ensureBase64($pack);
        $ssrLinks  = Util::urlSafeBase64Decode($pack);
        $subscribe = new static('empty subscribe');

        $ssrLinks = str_replace(["\r\n", "\r"], "\n", $ssrLinks);

        $ssrLinkArr = array_values(array_unique(explode("\n", $ssrLinks)));

        $haveName = false;

        for ($i = 0; $i < count($ssrLinkArr); $i++) {
            if (empty($ssrLinkArr[$i])) {
                continue;
            }
            $ssr = $ssrLinkArr[$i];
            if (substr($ssr, 0, 5) === 'ss://') {
                $subscribe->addSSR(SS::parseFromLink($ssr));
            } else if (substr($ssr, 0, 6) === 'ssr://') {
                $ssr = SSR::parseFromLink($ssr);
                $subscribe->addSSR($ssr);
                if (!$haveName && !empty($ssr->group)) {
                    $subscribe->name = $ssr->group;
                    $haveName = true;
                }
            }
        }

        if (empty(trim($subscribe->name))) {
            $subscribe->name = 'unname';
        }

        return $subscribe;
    }

    public function __toString()
    {
        $group = $this->name;
        return Util::urlSafeBase64Encode(implode("\r\n", array_map(function ($ssr) use ($group) {
            $ssr->group = $group;
            return (string) $ssr;
        }, $this->SSRs)));
    }

    public function count()
    {
        return count($this->SSRs);
    }

    public function current()
    {
        return $this->SSRs[$this->position];
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
        return isset($this->SSRs[$this->position]);
    }

    public function offsetExists($offset)
    {
        return isset($this->SSRs[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->SSRs[$offset]) ? $this->SSRs[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->SSRs[] = $value;
        } else {
            $this->SSRs[$offset] = $value;
        }
    }
    public function offsetUnset($offset)
    {
        unset($this->SSRs[$offset]);
    }
}
