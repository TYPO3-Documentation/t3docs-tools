<?php

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GitHub\GithubRepository;

$loader = require 'vendor/autoload.php';

$config = Configuration::getInstance()->getConfiguration();

$gitRepository = new GithubRepository();
$names = $gitRepository->getNames();

foreach($names as $name) {
    print("$name\n");
}
