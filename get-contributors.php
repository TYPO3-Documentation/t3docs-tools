<?php

use T3docs\T3docsTools\GitHub\GithubRepository;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: php get-contributors.php [<year>] [<month>] [<type>] [<user>] [<repo>] [<token>]\n");
    print("\n");
    print("Arguments:\n");
    print("   year: Consider commits of this year, \"0\" means the current year. [default: \"0\"]\n");
    print("   month: Consider commits of this month, \"0\" means all months. [default: \"0\"]\n");
    print("   type: Consider all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]\n");
    print("   user: Consider the repositories of this GitHub user namespace (typo3-documentation, typo3, friendsoftypo3), which has to be defined in the /config.yml or /config.local.yml. [default: \"typo3-documentation\"]\n");
    print("   repo: Consider commits of this specific repository, \"\" means of all repositories. [default: \"\"]\n");
    print("   token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: \"\"]\n");
    exit(1);
}

if ($argc > 7) {
    usage();
}

$year = $argv[1] ?? 0;
$month = $argv[2] ?? 0;
$type = $argv[3] ?? 'docs';
$user = $argv[4] ?? 'typo3-documentation';
$repo = $argv[5] ?? '';
$token = $argv[6] ?? '';

$gitRepository = new GithubRepository($token);
$gitRepository->fetchRepos($user, $type);
$contributors = $gitRepository->fetchContributors($repo, $year, $month);

$year = $year == 0 ? intval(date("Y")) : $year;
$filename = $month == 0 ? 'contributors-' . $year . '.csv' : 'contributors-' . $year . '_' . $month . '.csv';
$file = fopen($filename, 'w');
fwrite($file,"count,name,email,id\n");
foreach($contributors as $id => $contributor) {
    $line = $contributor['count'] . ',' . $contributor['name'] . ',' . $contributor['email'] . ',' . $id;
    fwrite($file, $line . "\n");
}
fclose($file);

print("----------------------\n");
print("------- Results ------\n");
print("----------------------\n\n");
print("Number of contributors:" . count($contributors) . "\n");
print("Wrote list: $filename\n");


