#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
thisdir=$(pwd)

# config
source $thisdir/config.sh

function usage()
{
    echo "Usage: $0 <version>"
    echo ""
    echo "Arguments:"
    echo "   version: List all local repositories not having a branch matching this version."
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

if [ $# -ne 1 ]; then
    usage
fi

if [ ! -d "$repodir" ]; then
    exitMsg "The TYPO3 documentation repositories are not pulled to \"$repodir\" yet. Run get-repos.sh first."
fi

version=$1

cd "$repodir"
for repo in TYPO3CMS*; do
    cd "$repodir/$repo"

    git branch -a | grep "remotes\/origin\/$version" >/dev/null
    if [ $? -ne 0 ]; then
        echo "$repo is missing version $version."
    fi
done
