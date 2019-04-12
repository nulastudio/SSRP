<?php

namespace nulastudio\SSR;

use nulastudio\SSR\SS;
use nulastudio\SSR\SSR;
use nulastudio\SSR\Util;

class SSRSubscribe
{
    protected $SSRs = [];
    public $name    = 'empty subscribe';

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
            $this->SSRs[] = SSR::parseFromSS(SS);
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
        $ssrLinks  = Util::urlSafeBase64Decode($pack);
        $subscribe = new static('empty subscribe');

        $ssrLinkArr = explode("\r\n", $ssrLinks);

        for ($i = 0; $i < count($ssrLinkArr); $i++) {
            $ssr = SSR::parseFromLink($ssrLinkArr[$i]);
            $subscribe->addSSR($ssr);
            if ($i === 1) {
                $subscribe->name = $ssr->group;
            }
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
}
