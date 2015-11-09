<?php
namespace PrestaShop\ModuleAnalyzer;

@ini_set('display_errors', 'on');

require implode(DIRECTORY_SEPARATOR, [
    __DIR__, 'vendor', 'autoload.php'
]);

$usage = function ($msg = null) {
    echo "Usage: php prestashop-module-analyzer/analyze.php path/to/report.json\n";
    if (null !== $msg) {
        echo "$msg\n";
    }
    exit(0);
};

if (!isset($argv[1]) || !file_exists($argv[1])) {
    $usage("Please provide the path to a .json report.");
}

$path = $argv[1];

$analyzer = new Analyzer;
$analyzer
    ->loadReport($path)
    ->summarize()
;
