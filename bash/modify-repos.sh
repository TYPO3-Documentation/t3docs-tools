#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 set-fork <fork> [<user>] [<token>]"
    echo ""
    echo "Arguments:"
    echo "   fork: Set a remote \"fork\" repository if this GitHub user namespace has a repository with a matching name."
    echo "   user: Execute the action in the local repositories of this GitHub user namespace (all, $(getUsers 'all' ', ')). Multiple users must be separated by space, e.g. \"friendsoftypo3 typo3\". [default: \"typo3-documentation\"]"
    echo "   token: Use this GitHub API token to overcome GitHub rate limitations. [default: \"\"]"
    exit 1
}

function handleAction()
{
    if [ $# -lt 1 ]; then
        usage
    fi

    local action="$1"

    if [ "$action" = "set-fork" ]; then
        if [ $# -le 3 ]; then
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
    local user="${3:-typo3-documentation}"
    local token="${4:-}"

    local quiet=0
    local stopOnFirstHit=0
    local users=$(getUsers "$user" " ")
    if [ -z "$users" ]; then
        usage
    fi
    local forkRepos=""
    if [ -n "$forkUser" ]; then
        forkRepos=$(getRepoNames "all" "$forkUser" "$token" " ")
    fi

    declare -A modifyResults

    for user in $users; do
        userdir="$repodir/$user"

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
                git remote add fork "git@github.com:$forkUser/$repo.git"
                results="Repository https://github.com/$user/$repo has a fork."
            else
                results="Repository https://github.com/$user/$repo MISSES A FORK."
            fi
            if [ -n "$results" ] ; then
                modifyResults["$user/$repo|results"]=$results
                modifyResults["$user/$repo|numResults"]=1
                if [ $stopOnFirstHit -eq 1 ]; then
                    echo "Stopping on first hit."
                    break 2
                fi
            fi
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
