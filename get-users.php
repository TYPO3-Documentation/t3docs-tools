<?php

use T3docs\T3docsTools\Configuration;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: php get-users.php [<user>]\n");
    print("\n");
    print("Arguments:\n");
    print("   user: Consider this user namespace only, which has to be defined in the /config.yml or /config.local.yml. [default: \"all\"]\n");
    exit(1);
}

if ($argc > 2) {
    usage();
}

$user = $argv[1] ?? 'all';

$config = new Configuration();
$users = $config->getSortedFilteredUsers($user);

foreach($users as $user) {
    print("$user\n");
}
