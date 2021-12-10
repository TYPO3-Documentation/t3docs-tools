<?php

use T3docs\T3docsTools\GitHub\GithubRepository;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: php get-repo-names.php [<type>] [<user>] [<token>]\n");
    print("\n");
    print("Arguments:\n");
    print("   type: Consider all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]\n");
    print("   user: Consider the repositories of this GitHub user namespace (typo3-documentation, typo3), which has to be defined in the /config.yml. [default: \"typo3-documentation\"]\n");
    print("   token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: \"\"]\n");
    exit(1);
}

if ($argc > 4) {
    usage();
}

$type = $argv[1] ?? 'docs';
$user = $argv[2] ?? 'typo3-documentation';
$token = $argv[3] ?? '';

$gitRepository = new GithubRepository($token);
$gitRepository->fetchRepos($user, $type);
$repos = $gitRepository->getNames();

foreach($repos as $repo) {
    print("$repo\n");
}
