<?php

use T3docs\T3docsTools\Configuration;

$loader = require 'vendor/autoload.php';

function usage()
{
    $config = new Configuration();
    $hosts = $config->getSortedFilteredHosts('all');
    print("Usage: php get-hosts.php [<host>]\n");
    print("\n");
    print("Arguments:\n");
    print("   host: Consider this host only (all, " . implode(', ', $hosts) . "), which has to be defined in the /config.yml or /config.local.yml. [default: \"all\"]\n");
    exit(1);
}

if ($argc > 2) {
    usage();
}

$host = $argv[1] ?? 'all';

$config = new Configuration();
$hosts = $config->getSortedFilteredHosts($host);

foreach($hosts as $host) {
    print("$host\n");
}
