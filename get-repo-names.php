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
    print("Usage: php get-repo-names.php [<type>] [<host>] [<user>] [<token>]\n");
    print("\n");
    print("Arguments:\n");
    print("   type: Consider all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]\n");
    print("   host: Consider the repositories of this host (" . implode(', ', $hosts) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com\"]\n");
    print("   user: Consider the repositories of this user namespace (" . implode(', ', $users) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com:typo3-documentation\"]\n");
    print("   token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: \"\"]\n");
    exit(1);
}

if ($argc > 5) {
    usage();
}

$type = $argv[1] ?? 'docs';
$host = $argv[2] ?? 'github.com';
$user = $argv[3] ?? 'github.com:typo3-documentation';
$token = $argv[4] ?? '';

$config = new Configuration();
$repos = [];
if ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITHUB) {
    $gitRepository = new GithubRepository($host, $token);
    $gitRepository->fetchRepos($user, $type);
    $repos = $gitRepository->getNames();
} elseif ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITLAB) {
    $gitRepository = new GitLabRepository($host, $token);
    $gitRepository->fetchRepos($user, $type);
    $repos = $gitRepository->getNames();
}

foreach($repos as $repo) {
    print("$repo\n");
}
