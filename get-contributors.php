<?php
/**
 * - use private config for token
 * - use config for: year, etc.
 */

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GitHub\GithubRepository;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: get-contributors.php <year> [token]\n");
    exit(1);
}

if ($argv[1] ?? false) {
    $year = $argv[1];
} else {
    usage();
}

$token = null;
if ($argv[2] ?? false) {
    $token = $argv[2];
}

$config = Configuration::getInstance()->getConfiguration();

$gitRepository = new GithubRepository('all', $token);

$names = $gitRepository->getNames();

$contributors = $gitRepository->getContributors( $year);

$filename = 'contributors-' . $year . '.csv';

$file = fopen($filename, 'w');

fwrite($file,"count,name,email,id\n");
foreach($contributors as $id => $contributor) {
    $line = $contributor['count'] . ',' . $contributor['name'] . ',' . $contributor['email'] . ',' . $id;
    fwrite($file, $line . "\n");
}
fclose($file);

print("\n\n");
print("----------------------\n");
print("------- Results ------\n");
print("----------------------\n\n");
print("Number of contributors:" . count($contributors) . "\n");
print("Wrote list: $filename\n");


