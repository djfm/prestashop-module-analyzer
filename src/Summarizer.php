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
                            $overridenMethods[$fqMethod] = [];
                        }
                        $overridenMethods[$fqMethod][] = $report->getModuleName();
                    }
                }
            }

            foreach ($report->getRegisteredHooks() as $hook) {
                if (!isset($registeredHooks[$hook])) {
                    $registeredHooks[$hook] = [];
                }
                $registeredHooks[$hook][] = $report->getModuleName();
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

        $sheet->setTitle('Summary');
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
        foreach (array_slice($overridenMethods, 0, $limit) as $fqMethod => $modules) {
            $popularOverrides[] = sprintf('%1$s (%2$d)', $fqMethod, count($modules));
        }
        echo sprintf("\nTop $limit most popular overrides:\n%s\n", implode(', ', $popularOverrides));
        $overridesSheet = $xl->createSheet(1);
        $overridesSheet->setTitle('Overrides');
        $overridesSheet->setCellValue('A1', 'Overriden Method');
        $overridesSheet->setCellValue('B1', '#Overriding Modules');
        $overridesSheet->setCellValue('C1', 'Overriding Modules...');
        $row = 2; $col = 0;
        foreach ($overridenMethods as $method => $modules) {
            $overridesSheet
                ->getCellByColumnAndRow($col, $row)
                ->setValue($method)
            ;
            $overridesSheet
                ->getCellByColumnAndRow($col + 1, $row)
                ->setValue(count($modules))
            ;
            foreach ($modules as $colOffset => $module) {
                $overridesSheet
                    ->getCellByColumnAndRow($col + 1 + $colOffset + 1, $row)
                    ->setValue($module)
                ;
            }
            ++$row;
        }

        $popularHooks = [];
        foreach (array_slice($registeredHooks, 0, $limit) as $hook => $modules) {
            $popularHooks[] = sprintf('%1$s (%2$d)', $hook, count($modules));
        }
        echo sprintf("\nTop $limit most popular hooks:\n%s\n", implode(', ', $popularHooks));
        $hooksSheet = $xl->createSheet(2);
        $hooksSheet->setTitle('Registered Hooks');
        $hooksSheet->setCellValue('A1', 'Hook');
        $hooksSheet->setCellValue('B1', '#Registering Modules');
        $hooksSheet->setCellValue('C1', 'Registering Modules...');
        $row = 2; $col = 0;
        foreach ($registeredHooks as $hook => $modules) {
            $hooksSheet
                ->getCellByColumnAndRow($col, $row)
                ->setValue($hook)
            ;
            $hooksSheet
                ->getCellByColumnAndRow($col + 1, $row)
                ->setValue(count($modules))
            ;
            foreach ($modules as $colOffset => $module) {
                $hooksSheet
                    ->getCellByColumnAndRow($col + 1 + $colOffset + 1, $row)
                    ->setValue($module)
                ;
            }
            ++$row;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($xl);
        $objWriter->save($target);
    }
}
