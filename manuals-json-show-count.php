<?php

use T3docs\T3docsTools\DocsServer\ManualsJson;

$loader = require 'vendor/autoload.php';

$fileName = null;
if ($argv[1] ?? false) {
    $fileName = $argv[1];
}

$manualsJson = new ManualsJson();
try {
    $ret = $manualsJson->readFile($fileName);
    if (!$ret) {
        print("ERROR: error reading file $fileName\n");
        exit(1);
    }
} catch (Exception $e)
{
    print("ERROR: error reading file $fileName:" . $e.getMessage() . "\n");
    exit(1);
}
$manualsJson->printCount();
