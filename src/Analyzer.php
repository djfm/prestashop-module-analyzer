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

    public function loadReport($path)
    {
        if (!file_exists($path)) {
            throw new Exception(
                sprintf('Could not open .json file `%s`.', $path)
            );
        }

        $data = json_decode(file_get_contents($path), true);

        foreach ($data as $moduleName => $moduleData) {
            $this->addReport((new ModuleReport($moduleName))->fromArray($moduleData));
        }

        return $this;
    }

    public function summarize()
    {
        echo "\n";
        $nModules = count($this->getReports());
        $nWithOverrides = 0;

        $overridenMethods = [];
        $registeredHooks = [];

        foreach ($this->getReports() as $report) {
            if (!empty($report->getOverrides())) {
                ++$nWithOverrides;
                foreach ($report->getOverrides() as $class => $methods) {
                    foreach ($methods as $method) {
                        $fqMethod = "$class::$method";
                        if (!isset($overridenMethods[$fqMethod])) {
                            $overridenMethods[$fqMethod] = 0;
                        }
                        ++$overridenMethods[$fqMethod];
                    }
                }
            }

            foreach ($report->getRegisteredHooks() as $hook) {
                if (!isset($registeredHooks[$hook])) {
                    $registeredHooks[$hook] = 0;
                }
                ++$registeredHooks[$hook];
            }
        }
        arsort($overridenMethods);
        arsort($registeredHooks);

        echo sprintf("Analyzed modules: %d\n", $nModules);
        echo sprintf("Containing overrides: %1\$d (%2\$s%%)\n",
            $nWithOverrides,
            round(100 * $nWithOverrides / $nModules, 2)
        );

        $limit = 50;

        $popularOverrides = [];
        foreach (array_slice($overridenMethods, 0, $limit) as $fqMethod => $n) {
            $popularOverrides[] = sprintf('%1$s (%2$d)', $fqMethod, $n);
        }
        echo sprintf("\nTop $limit most popular overrides:\n%s\n", implode(', ', $popularOverrides));

        $popularHooks = [];
        foreach (array_slice($registeredHooks, 0, $limit) as $hook => $n) {
            $popularHooks[] = sprintf('%1$s (%2$d)', $hook, $n);
        }
        echo sprintf("\nTop $limit most popular hooks:\n%s\n", implode(', ', $popularHooks));
    }
}
