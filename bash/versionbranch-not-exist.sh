#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 <version> [<host>] [<user>]"
    echo ""
    echo "Arguments:"
    echo "   version: List all local repositories not having a branch matching this version."
    echo "   host: List local repositories of this host (all, $(getHosts 'all' ', ')). Multiple hosts must be separated by space, e.g. \"github.com gitlab.com\". [default: \"all\"]"
    echo "   user: List local repositories of this user namespace (all, $(getUsers 'all' 'all' ', ')). Multiple users must be separated by space, e.g. \"github.com:friendsoftypo3 github.com:typo3\". [default: \"all\"]"
    exit 1
}

function handleRequest()
{
    if [ $# -ge 1 ] && [ $# -le 3 ]; then
        versionbranchNotExists "$@"
    else
        usage
    fi
}

function versionbranchNotExists()
{
    local version=$1
    local host="${2:-all}"
    local user="${3:-all}"

    local hosts=$(getHosts "$host" " ")
    local users=$(getUsers "$host" "$user" " ")
    if [ -z "$hosts" ]; then
        echo "Cannot find any configured host for given \"$host\" in /config.yml or /config.local.yml."
        echo "---"
        usage
    elif [ -z "$users" ]; then
        echo "Cannot find any configured user for given host \"$host\" and user \"$user\" in /config.yml or /config.local.yml."
        echo "---"
        usage
    fi

    for host in $hosts; do
        for user in $users; do
            if [[ ! $user =~ ^$host: ]]; then
                continue
            fi

            user=$(echo "$user" | awk -F':' '{print $2}')
            userdir="$repodir/$host/$user"

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
                    echo "$host/$user/$repo is missing version $version."
                fi
            done
        done
    done
}

handleRequest "$@"
