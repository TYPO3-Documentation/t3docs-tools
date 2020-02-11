<?php

use T3docs\T3docsTools\Changelog\GenerateChangelogIssue;

function usage()
{
    print("php -f generate-changelog-issue.php <url to changelog or version> [id of existing issue]\n");
    exit(1);
}

if (!($argv[1] ?? false)) {
    usage();
}
$url = $argv[1];

if (strpos($url, 'https:', 0) !== 0) {
    $url = 'https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/' . $url . '/Index.html';
    print("Use url: $url\n");
}

$loader = require 'vendor/autoload.php';
$changelog = new GenerateChangelogIssue();


if ($argv[2] ?? false) {
    // show new changelogs
    $issueId = (int) ($argv[2]);
    // load existing issue
    $result = $changelog->getChangesFromIssue($issueId);

    //var_dump($result);
    //exit(0);

    $changelog->getNewChangelogs($url);
    print("New changelogs\n");
    $changelog->printChangelogs();

} else {
    // show all changelogs
    $changes = $changelog->getChangelog($url);
    $changelog->printChangelogs();
}