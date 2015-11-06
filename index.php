<?php
namespace PrestaShop\ModuleAnalyzer;

@ini_set('display_errors', 'on');

require implode(DIRECTORY_SEPARATOR, [
    __DIR__, 'vendor', 'autoload.php'
]);

$target = 'module-analyzer-report.json';

$usage = function ($msg = null) use ($target) {
    echo "Usage: php prestashop-module-analyzer/index.php path/to/module.zip [output file, default: $target]\n";
    if (null !== $msg) {
        echo "$msg\n";
    }
    exit(0);
};

if (!isset($argv[1]) || !file_exists($argv[1])) {
    $usage("Please provide the path to a PrestaShop module zip archive or to a directory containing zip archives.");
}

$path = $argv[1];

if (isset($argv[2])) {
    $target = $argv[2];
}

$analyzer = new Analyzer;
$analyzer
    ->analyze($path)
    ->writeReport($target)
;

$n = count($analyzer->getReports());
echo "Done analyzing $n modules.\n";
