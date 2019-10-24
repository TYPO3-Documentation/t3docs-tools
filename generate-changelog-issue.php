<?php

use T3docs\T3docsTools\Changelog\GenerateChangelogIssue;

$loader = require 'vendor/autoload.php';
$changelog = new GenerateChangelogIssue();

$changelog->getChangelog($argv[1]);

