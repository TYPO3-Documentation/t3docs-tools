#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(pwd)


# default configuration
generateddir=$thisdir/../generated-data
repodir=$generateddir/repos
phpdir=$thisdir/../

# override with custom configuration
if [ -f $thisdir/config.local.sh ]; then
    source $thisdir/config.local.sh
fi
