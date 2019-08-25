<?php

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GithubRepository;

$loader = require 'vendor/autoload.php';

$config = Configuration::getInstance()->getConfiguration();

$gitRepository = new GithubRepository();
$names = $gitRepository->getNames();
var_dump($names);