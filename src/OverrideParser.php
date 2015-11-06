<?php

namespace PrestaShop\ModuleAnalyzer;

class OverrideParser
{
    public function parseOverride($contents)
    {
        $regExp = "/^\\s*class\\s+(\\w+)\\s+extends\\s+\\1Core\\b/m";

        $m = [];

        if (preg_match($regExp, $contents, $m)) {
            $className = $m[1];
            return [$className => $this->parseFunctions($contents)];
        } else {
            return [];
        }
    }

    private function parseFunctions($contents)
    {
        $regExp     = "/^\\s*(?:(?:public|protected)\\s+)?function\\s+(\\w+)\\s*\\(/m";
        $matches    = [];
        preg_match_all($regExp, $contents, $matches);
        return $matches[1];
    }
}
