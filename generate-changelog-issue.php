<?php

use T3docs\T3docsTools\Changelog\GenerateChangelogIssue;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: php generate-changelog-issue.php <url> [<issue>] [<token>]\n");
    print("\n");
    print("Arguments:\n");
    print("   url: Absolute changelog URL or TYPO3 version. For example \"https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.5/Index.html\" or \"11.5\".\n");
    print("   issue: ID of existing issue. If empty, all issues of changelog URL are printed. [default: \"\"]\n");
    print("   token: Fetch the changelog issues using this GitHub API token to overcome rate limitations. [default: \"\"]\n");
    exit(1);
}

if ($argc < 2 || $argc > 4) {
    usage();
}

$url = $argv[1];
$issue = $argv[2] ?? '';
$token = $argv[3] ?? '';

if (!str_starts_with($url, 'https:')) {
    $url = 'https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/' . $url . '/Index.html';
    print("Use url: $url\n");
}

$changelog = new GenerateChangelogIssue($token);

if (!empty($issue)) {
    $issueId = (int)$issue;
    $result = $changelog->getChangesFromIssue($issueId);
    $changelog->getNewChangelogs($url);
    print("New changelogs\n");
    $changelog->printChangelogs();
} else {
    $changes = $changelog->getChangelog($url);
    $changelog->printChangelogs();
}
