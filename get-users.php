<?php

use T3docs\T3docsTools\Configuration;

$loader = require 'vendor/autoload.php';

function usage()
{
    $config = new Configuration();
    $hosts = $config->getSortedFilteredHosts('all');
    $users = $config->getSortedFilteredUsers('all', 'all');
    print("Usage: php get-users.php [<host>] [<user>]\n");
    print("\n");
    print("Arguments:\n");
    print("   host: Consider this host only (all, " . implode(', ', $hosts) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"all\"]\n");
    print("   user: Consider this user namespace only (all, " . implode(', ', $users) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"all\"]\n");
    exit(1);
}

if ($argc > 3) {
    usage();
}

$host = $argv[1] ?? 'all';
$user = $argv[2] ?? 'all';

$config = new Configuration();
$users = $config->getSortedFilteredUsers($host, $user);

foreach($users as $user) {
    print("$user\n");
}
