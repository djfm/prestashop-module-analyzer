<?php
namespace PrestaShop\ModuleAnalyzer;

@ini_set('display_errors', 'on');

require implode(DIRECTORY_SEPARATOR, [
    __DIR__, 'vendor', 'autoload.php'
]);

$target = 'summary.xlsx';

$usage = function ($msg = null) use ($target) {
    echo "Usage: php prestashop-module-analyzer/analyze.php path/to/report.json [output file, default: $target]\n";
    if (null !== $msg) {
        echo "$msg\n";
    }
    exit(0);
};

if (!isset($argv[1]) || !file_exists($argv[1])) {
    $usage("Please provide the path to a .json report.");
}

$path = $argv[1];

if (isset($argv[2])) {
    $target = $argv[2];
}

$analyzer = new Analyzer;
$analyzer
    ->loadReport($path)
    ->summarize($target)
;
