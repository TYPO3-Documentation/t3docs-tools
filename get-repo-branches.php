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
    print("Usage: php get-repo-branches.php [<type>] [<host>] [<user>] [<token>] [<force>]\n");
    print("\n");
    print("Arguments:\n");
    print("   type: Consider all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]\n");
    print("   host: Consider the repositories of this host (all, " . implode(', ', $hosts) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com\"]\n");
    print("   user: Consider the repositories of this user namespace (all, " . implode(', ', $users) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"github.com:typo3-documentation\"]\n");
    print("   token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: \"\"]\n");
    print("   force: Allow user namespaces not configured in the /config.yml or /config.local.yml. Requires a specific user namespace, not the generic \"all\". [default: 0]\n");
    exit(1);
}

if ($argc > 6) {
    usage();
}

$type = $argv[1] ?? 'docs';
$host = $argv[2] ?? 'github.com';
$user = $argv[3] ?? 'github.com:typo3-documentation';
$token = $argv[4] ?? '';
$force = (bool)($argv[5] ?? 0);

$config = new Configuration();
if (!$force) {
    $users = $config->getSortedFilteredUsers($host, $user);
} else {
    $users = $config->getSortedFilteredUsers($host, $user, Configuration::CHECK_HOST_ONLY);
}

$repos = [];
foreach ($users as $userIdentifier) {
    list($host, $user) = explode(':', $userIdentifier, 2);
    if ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITHUB) {
        $gitRepository = new GithubRepository($host, $token);
        $gitRepository->fetchRepos($user, $type);
        $repos[$userIdentifier] = $gitRepository->getNames();
    } elseif ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITLAB) {
        $gitRepository = new GitLabRepository($host, $token);
        $gitRepository->fetchRepos($user, $type);
        $repos[$userIdentifier] = $gitRepository->getNames();
    }
}

foreach ($repos as $userIdentifier => $reposByUser) {
    list($host, $user) = explode(':', $userIdentifier, 2);
    foreach ($reposByUser as $repo) {
        print("{$userIdentifier}:{$repo}\n");

        $branches = [];
        if ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITHUB) {
            $gitRepository = new GithubRepository($host, $token);
            $branches = $gitRepository->fetchBranchNamesOfRepo($user, $repo);
        } elseif ($config->getTypeOfHost($host) === Configuration::HOST_TYPE_GITLAB) {
            $gitRepository = new GitLabRepository($host, $token);
            $branches = $gitRepository->fetchBranchNamesOfRepo($user, $repo);
        }

        foreach ($branches as $branch) {
            print("  $branch \n");
        }
    }
}
