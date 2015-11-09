<?php

namespace PrestaShop\ModuleAnalyzer;

class Summarizer
{
    public function summarize(array $reports, $target)
    {
        echo "\n";
        $nModules = count();
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
