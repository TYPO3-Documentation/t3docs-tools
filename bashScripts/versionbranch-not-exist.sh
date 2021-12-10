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
    echo "Usage: $0 <version> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   version: List all local repositories not having a branch matching this version."
    echo "   user: List local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: \"typo3-documentation\"]"
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

if [ $# -lt 1 ] || [ $# -gt 2 ]; then
    usage
fi

version=$1
user="${2:-typo3-documentation}"
if [ "$user" = "all" ]; then
    users="typo3-documentation typo3"
elif [ "$user" = "typo3-documentation" ] || [ "$user" = "typo3" ]; then
    users="$user"
else
    usage
fi

for user in $users; do
    userdir="$repodir/$user"

    if [ ! -d "$userdir" ]; then
        exitMsg "The TYPO3 repositories are not pulled to \"$userdir\" yet. Run get-repos.sh first."
    fi

    cd "$userdir"
    for repo in *; do
        if [ ! -d "$userdir/$repo" ]; then
            break;
        fi

        cd "$userdir/$repo"

        git branch -a | grep "remotes\/origin\/$version" >/dev/null
        if [ $? -ne 0 ]; then
            echo "$repo is missing version $version."
        fi
    done
done
