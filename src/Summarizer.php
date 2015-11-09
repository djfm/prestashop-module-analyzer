<?php

namespace PrestaShop\ModuleAnalyzer;

use PHPExcel;
use PHPExcel_Writer_Excel2007;

class Summarizer
{
    public function summarize(array $reports, $target)
    {
        $xl     = new PHPExcel;
        $sheet  = $xl->getActiveSheet();

        echo "\n";
        $nModules = count($reports);
        $nWithOverrides = 0;

        $overridenMethods = [];
        $registeredHooks = [];

        foreach ($reports as $report) {
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

        $shareWithOverrides = $nWithOverrides / $nModules;
        echo sprintf("Analyzed modules: %d\n", $nModules);
        echo sprintf("Containing overrides: %1\$d (%2\$s%%)\n",
            $nWithOverrides,
            round(100 * $shareWithOverrides, 2)
        );

        $sheet->setCellValue('A1', 'Analyzed modules');
        $sheet->setCellValue('B1', $nModules);

        $sheet->setCellValue('A2', 'Containing overrides');
        $sheet->setCellValue('B2', $nWithOverrides);
        $sheet->setCellValue('C2', $shareWithOverrides);
        $sheet->getStyle('C2')->getNumberFormat()->applyFromArray([
            'code' => \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00
        ]);

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

        $objWriter = new PHPExcel_Writer_Excel2007($xl);
        $objWriter->save($target);
    }
}
