#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
thisdir=$(pwd)

source $thisdir/config.sh
source $thisdir/helpers.sh

function usage()
{
    echo "Usage: $0 <version> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   version: List all local repositories having a branch matching this version."
    echo "   user: List local repositories of this GitHub user namespace (all, $(getUsers 'all' ', ')). Multiple users must be separated by space, e.g. \"friendsoftypo3 typo3\". [default: \"typo3-documentation\"]"
    exit 1
}

if [ $# -lt 1 ] || [ $# -gt 2 ]; then
    usage
fi

version=$1
user="${2:-typo3-documentation}"

users=$(getUsers "$user" " ")
if [ -z "$users" ]; then
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
        if [ $? -eq 0 ]; then
            echo "$repo has version $version."
        fi
    done
done
