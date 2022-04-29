<?php

use T3docs\T3docsTools\Configuration;

$loader = require 'vendor/autoload.php';

function usage()
{
    print("Usage: php get-hosts-config.php [<host>]\n");
    print("\n");
    print("Arguments:\n");
    print("   host: Consider this host only, which has to be defined in the /config.yml or /config.local.yml. [default: \"all\"]\n");
    exit(1);
}

if ($argc > 3) {
    usage();
}

$host = $argv[1] ?? 'all';

$config = new Configuration();
$hosts = $config->getSortedFilteredHosts($host);

print("(\n");
foreach ($hosts as $host) {
    printf("[\"%s:type\"]=\"%s\"\n", $host, $config->getTypeOfHost($host));
    printf("[\"%s:http_url\"]=\"%s\"\n", $host, $config->getHttpUrlOfHost($host));
    printf("[\"%s:ssh_url\"]=\"%s\"\n", $host, $config->getSshUrlOfHost($host));
    printf("[\"%s:api_url\"]=\"%s\"\n", $host, $config->getApiUrlOfHost($host));
}
print(")\n");
