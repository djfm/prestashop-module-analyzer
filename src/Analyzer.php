<?php

namespace PrestaShop\ModuleAnalyzer;

use ZipArchive;

class Analyzer
{
    public function analyze($path)
    {
        return $this->analyzeZip($path);
    }

    private function analyzeZip($path)
    {
        $moduleName = basename($path, '.zip');
        $report     = new ModuleReport($moduleName);

        $za = new ZipArchive;

        if (!file_exists($path) || true !== $za->open($path)) {
            throw new Exception(
                sprintf('Could not open archive `%s`.', $path)
            );
        }

        for ($i = 0; $i < $za->numFiles; ++$i) {
            $filename = $za->getNameIndex($i);
            if (preg_match('/\.php$/', $filename)) {
                $contents = file_get_contents("zip://$path#$filename");
                $report->parsePhpFile($filename, $contents);
            }
        }

        return $report;
    }
}
