#!/bin/bash

thisdir=$(dirname $(realpath "$0"))

# default configuration
phpdir=$(realpath "$thisdir/..")
generateddir=$(realpath "$thisdir/../generated-data")
repodir=$generateddir/repos

# override with custom configuration
if [ -f $thisdir/config.local.sh ]; then
    source $thisdir/config.local.sh
fi
