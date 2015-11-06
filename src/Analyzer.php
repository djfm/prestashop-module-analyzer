<?php

namespace PrestaShop\ModuleAnalyzer;

use Exception;
use ZipArchive;

class Analyzer
{
    private $reports = [];

    public function addReport(ModuleReport $report)
    {
        echo "$report\n";
        $this->reports[] = $report;
        return $this;
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function analyze($path)
    {
        $zips = [];

        if (is_dir($path)) {
            $zips = glob("$path/*.zip");
        } else {
            $zips = [$path];
        }

        foreach ($zips as $zip) {
            $this->addReport($this->analyzeZip($zip));
        }

        return $this;
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

    public function writeReport($path)
    {
        $data = [];

        foreach ($this->getReports() as $report) {
            $data[$report->getModuleName()] = $report->toArray();
        }

        file_put_contents(
            $path,
            json_encode(
                $data,
                JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
            )
        );

        return $this;
    }
}
