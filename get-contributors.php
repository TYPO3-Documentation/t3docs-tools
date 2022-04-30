<?php

use T3docs\T3docsTools\Configuration;
use T3docs\T3docsTools\GitHub\GithubRepository;
use T3docs\T3docsTools\GitLab\GitLabRepository;

$loader = require 'vendor/autoload.php';

function usage()
{
    $config = new Configuration();
    $hosts = $config->getSortedFilteredHosts('all');
    $users = $config->getSortedFilteredUsers('all', 'all');
    print("Usage: php get-contributors.php [<year>] [<month>] [<type>] [<host>] [<user>] [<repo>] [<token>]\n");
    print("\n");
    print("Arguments:\n");
    print("   year: Consider commits of this year, \"0\" means the current year. [default: \"0\"]\n");
    print("   month: Consider commits of this month, \"0\" means all months. [default: \"0\"]\n");
    print("   type: Consider all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]\n");
    print("   host: Consider the repositories of this host (all, " . implode(', ', $hosts) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com\"]\n");
    print("   user: Consider the repositories of this user namespace (all, " . implode(', ', $users) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com:typo3-documentation\"]\n");
    print("   repo: Consider commits of this specific repository, \"\" means of all repositories. [default: \"\"]\n");
    print("   token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: \"\"]\n");
    exit(1);
}

if ($argc > 8) {
    usage();
}

$year = $argv[1] ?? 0;
$month = $argv[2] ?? 0;
$type = $argv[3] ?? 'docs';
$host = $argv[4] ?? 'github.com';
$user = $argv[5] ?? 'github.com:typo3-documentation';
$repo = $argv[6] ?? '';
$token = $argv[7] ?? '';

$config = new Configuration();
$users = $config->getSortedFilteredUsers($host, $user);

$contributors = [];
foreach($users as $userIdentifier) {
    list($host, $user) = explode(':', $userIdentifier, 2);
    if ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITHUB) {
        $gitRepository = new GithubRepository($host, $token);
        $gitRepository->fetchRepos($user, $type);
        $contributors[$userIdentifier] = $gitRepository->fetchContributors($repo, $year, $month);
    } elseif ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITLAB) {
        $gitRepository = new GitLabRepository($host, $token);
        $gitRepository->fetchRepos($user, $type);
        $contributors[$userIdentifier] = $gitRepository->fetchContributors($repo, $year, $month);
    }
}

foreach ($contributors as $userIdentifier => $contributorByUser) {
    $namespace = empty($repo) ? "{$userIdentifier}" : "{$userIdentifier}:{$repo}";
    $year = $year == 0 ? intval(date("Y")) : $year;
    $date = $month == 0 ? "{$year}" : "{$year}_{$month}";
    $filename = "contributors_{$namespace}_{$date}.csv";

    $data = [];
    $data[] = "count,name,email,id";
    foreach ($contributorByUser as $id => $contributor) {
        $data[] = $contributor['count'] . ',' . $contributor['name'] . ',' . $contributor['email'] . ',' . $id;
    }
    file_put_contents($filename, implode("\n", $data));

    print("----------------------\n");
    print("------- Results ------\n");
    print("----------------------\n\n");
    print("Namespace: $namespace \n");
    print("Number of contributors: " . (count($data)-1) . "\n");
    print("Wrote list: $filename\n");
}
