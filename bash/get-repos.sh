#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 [<type>] [<host>] [<user>] [<token>]"
    echo ""
    echo "Arguments:"
    echo "   type: Fetch all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"all\"]"
    echo "   host: Fetch the repositories of this host (all, $(getHosts 'all' ', ')), which has to be defined in the /config.yml or /config.local.yml. Multiple hosts must be separated by space, e.g. \"github.com gitlab.com\". [default: \"all\"]"
    echo "   user: Fetch the repositories of this user namespace (all, $(getUsers 'all' 'all' ', ')), which has to be defined in the /config.yml or /config.local.yml. Multiple users must be separated by space, e.g. \"github.com:friendsoftypo3 github.com:typo3\". [default: \"all\"]"
    echo "   token: Fetch the repositories using this GitHub / GitLab API token to overcome rate limitations. [default: \"\"]"
    exit 1
}

function handleRequest()
{
    if [ $# -le 4 ]; then
        getRepos "$@"
    else
        usage
    fi
}

function getRepos()
{
    local type="${1:-all}"
    local host="${2:-all}"
    local user="${3:-all}"
    local token="${4:-}"

    if [ "$type" != "all" ] && [ "$type" != "docs" ]; then
        echo "The given type \"$type\" is invalid."
        echo "---"
        usage
    fi

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

    declare -A hostsConfig=$(getHostsConfig "$host")

    for host in $hosts; do
        for user in $users; do
            if [[ ! $user =~ ^$host: ]]; then
                continue
            fi

            user=$(echo "$user" | awk -F':' '{print $2}')
            userdir="$repodir/$host/$user"

            if ! mkdir -p "$userdir"; then
                exitMsg "Error creating directory \"$userdir\"."
            fi

            echo "Clone or update local repositories of"
            echo "$userdir/."
            echo "------------------------------------------------------------------------"

            getRepoNames "$type" "$host" "$user" "$token" "0" "\n" | while read repo; do
                cd "$userdir"
                if [ ! -d "$repo" ]; then
                    echo "Cloning repo $repo."
                    git clone $(printf ${hostsConfig["$host:ssh_url"]} "$user" "$repo") || exitMsg "clone $repo"
                else
                    echo "$repo already exists: Update remote tracking branches, checkout and update main branch."
                    cd "$repo"
                    # Update remote tracking branches
                    git fetch --prune || exitMsg "fetch $repo"
                    # Checkout and update main branch
                    mainbranch=""
                    for branch in main master latest; do
                        exists=$(git branch -a --list "origin/$branch")
                        if [ -n "$exists" ]; then
                            mainbranch="$branch"
                            break
                        fi
                    done
                    if [ -n "$mainbranch" ]; then
                        git checkout -B $mainbranch origin/$mainbranch || exitMsg "checkout $mainbranch based on origin/$mainbranch in $repo"
                    else
                        echo "The $repo repo is not yet initialized because it lacks a main branch."
                    fi
                fi
                echo "------------------------------------------------------------------------"
            done
        done
    done
}

handleRequest "$@"
