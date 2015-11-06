<?php

namespace PrestaShop\ModuleAnalyzer;

class HookParser
{
    public function parseRegisteredHooks($contents)
    {
        $regExp     = "/\\bregisterHook\\s*\\(\\s*([\"'])(.*?)\\1\\s*\\)/i";
        $matches    = [];
        $n          = preg_match_all($regExp, $contents, $matches, PREG_SET_ORDER);

        $hooks      = [];

        for ($i = 0; $i < $n; ++$i) {
            $hooks[] = $matches[$i][2];
        }

        return array_unique($hooks);
    }

    public function parseAvailableHooks($contents)
    {
        $regExp     = "/^\\s*public\\s+function\\s+(?:hook(\\w+))\\s*\\(/m";
        $matches    = [];
        $n          = preg_match_all($regExp, $contents, $matches, PREG_SET_ORDER);

        $hooks      = [];

        for ($i = 0; $i < $n; ++$i) {
            $hooks[] = lcfirst($matches[$i][1]);
        }

        return array_unique($hooks);
    }
}
