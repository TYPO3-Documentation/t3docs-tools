#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 set-fork <fork> [<host>] [<user>] [<token>]"
    echo ""
    echo "Arguments:"
    echo "   fork: Set a remote \"fork\" repository if this GitHub user namespace has a repository with a matching name."
    echo "   host: Execute the action in the local repositories of this host (all, $(getHosts 'all' ', ')). Multiple hosts must be separated by space, e.g. \"github.com gitlab.com\". [default: \"all\"]"
    echo "   user: Execute the action in the local repositories of this user namespace (all, $(getUsers 'all' 'all' ', ')). Multiple users must be separated by space, e.g. \"github.com:friendsoftypo3 github.com:typo3\". [default: \"all\"]"
    echo "   token: Use this GitHub / GitLab API token to overcome rate limitations. [default: \"\"]"
    exit 1
}

function handleAction()
{
    if [ $# -lt 1 ]; then
        usage
    fi

    local action="$1"

    if [ "$action" = "set-fork" ]; then
        if [ $# -le 4 ]; then
            setFork "$@"
        else
            usage
        fi
    else
        usage
    fi
}

function setFork()
{
    local forkUser="${2:-}"
    local host="${3:-all}"
    local user="${4:-all}"
    local token="${5:-}"

    local quiet=0
    local stopOnFirstHit=0
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
    local forkRepos=""
    if [ -n "$forkUser" ]; then
        forkRepos=$(getRepoNames "all" "$host" "$forkUser" "$token" " ")
    fi

    declare -A hostsConfig=$(getHostsConfig "$host")
    declare -A modifyResults

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

            echo "Modify local repositories of $userdir/."

            cd "$userdir"
            for repo in *; do
                if [ ! -d "$userdir/$repo" ]; then
                    break;
                fi
                cd "$userdir/$repo"
                # Set "fork" remote
                git remote remove fork &> /dev/null
                if echo "$forkRepos" | grep -wq "$repo"; then
                    git remote add fork $(printf ${hostsConfig["$host:ssh_url"]} "$forkUser" "$repo")
                    git fetch --prune fork
                    results="Repository $(printf ${hostsConfig["$host:http_url"]} "$user" "$repo") has a fork."
                else
                    results="[MISSING] Repository $(printf ${hostsConfig["$host:http_url"]} "$user" "$repo") misses a fork."
                fi
                if [ -n "$results" ] ; then
                    modifyResults["$host/$user/$repo|results"]=$results
                    modifyResults["$host/$user/$repo|numResults"]=1
                    if [ $stopOnFirstHit -eq 1 ]; then
                        echo "Stopping on first hit."
                        break 3
                    fi
                fi
            done
        done
    done

    local totalResults=0
    # Sort results by repository name
    mapfile -d '' sorted < <(printf '%s\0' "${!modifyResults[@]}" | sort -z)
    # Print results
    for x in "${sorted[@]}"; do
        if [[ $x =~ \|results$ ]]; then
            searchPath=${x%|*}
            results=${modifyResults["$searchPath|results"]}
            numResults=${modifyResults["$searchPath|numResults"]}
            totalResults=$((totalResults+numResults))
            if [ $quiet -ne 1 ] ; then
                echo "$results"
            fi
        fi
    done
    if [ $totalResults -gt 0 ] ; then
        echo "------------------------------------------------------------------------"
        printf "Total: %s result(s)\n" "$totalResults"
        echo "------------------------------------------------------------------------"
    else
        echo "------------------------------------------------------------------------"
        echo "No results"
        echo "------------------------------------------------------------------------"
    fi
}

handleAction "$@"
