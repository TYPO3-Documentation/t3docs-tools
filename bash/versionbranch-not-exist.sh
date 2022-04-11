#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 <version> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   version: List all local repositories not having a branch matching this version."
    echo "   user: List local repositories of this GitHub user namespace (all, $(getUsers 'all' ', ')). Multiple users must be separated by space, e.g. \"friendsoftypo3 typo3\". [default: \"typo3-documentation\"]"
    exit 1
}

function handleRequest()
{
    if [ $# -ge 1 ] && [ $# -le 2 ]; then
        versionbranchNotExists "$@"
    else
        usage
    fi
}

function versionbranchNotExists()
{
    local version=$1
    local user="${2:-typo3-documentation}"

    local users=$(getUsers "$user" " ")
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
            if [ $? -ne 0 ]; then
                echo "$repo is missing version $version."
            fi
        done
    done
}

handleRequest "$@"
