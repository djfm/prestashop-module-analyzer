<?php
namespace PrestaShop\ModuleAnalyzer;

@ini_set('display_errors', 'on');

require implode(DIRECTORY_SEPARATOR, [
    __DIR__, 'vendor', 'autoload.php'
]);

function usage($msg = null) {
    echo "Usage: php prestashop-module-analyzer/index.php path/to/module.zip\n";
    if (null !== $msg) {
        echo "$msg\n";
    }
    exit(0);
}

if (!isset($argv[1]) || !file_exists($argv[1])) {
    usage("Please provide the path to a PrestaShop module zip archive.");
}

$path = $argv[1];

$analyzer = new Analyzer;
$analyzer->analyze($path);
