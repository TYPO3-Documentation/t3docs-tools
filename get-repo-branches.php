<?php

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GitHub\GithubRepository;

$loader = require 'vendor/autoload.php';

$config = Configuration::getInstance()->getConfiguration();

$type = 'docs';
if ($argv[1] ?? false) {
    $type = $argv[1];
}

$gitRepository = new GithubRepository($type);
$names = $gitRepository->getNames();
foreach($names as $name) {
    print("$name\n");

    $branches = $gitRepository->getBranchInfosForRepoName($name);
    foreach ($branches as $branch) {
        print("  $branch \n");
    }
}