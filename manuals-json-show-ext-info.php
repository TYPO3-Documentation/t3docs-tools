<?php

$filename = 'https://intercept.typo3.com/assets/docs/manuals.json';

function usage()
{
	print("Arguments: <extension key> [filename]\n");
	print("  for example: rtehtmlarea ../tmp/manuals.json\n");
	exit(0);
}

if ($argv[2] ?? false) {
    $filename = $argv[2];
}
if ($argv[1] ?? false) {
    $ext = $argv[1];
} else {
	usage();
	exit(1);
}

$content = file_get_contents($filename);
$json = json_decode($content, true);
if ($ext ?? false) {
	var_dump($json[$ext]);
}