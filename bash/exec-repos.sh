#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 <command> [<host>] [<user>]"
    echo ""
    echo "Arguments:"
    echo "   command: Execute this command in all branches of all local repositories. This parameter can also be the absolute file path of a bash script."
    echo "   host: Execute the command in the local repositories of this host (all, $(getHosts 'all' ', ')). Multiple hosts must be separated by space, e.g. \"github.com gitlab.com\". [default: \"all\"]"
    echo "   user: Execute the command in the local repositories of this user namespace (all, $(getUsers 'all' 'all' ', ')). Multiple users must be separated by space, e.g. \"github.com:friendsoftypo3 github.com:typo3\". [default: \"all\"]"
    exit 1
}

function handleRequest()
{
    if [ $# -ge 1 ] && [ $# -le 3 ]; then
        execRepos "$@"
    else
        usage
    fi
}

function execRepos()
{
    local cmd="$1"
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

    local quiet=0
    local stopOnFirstHit=0
    local isCmdFile=0
    if [ -f "$cmd" ]; then
        isCmdFile=1
    fi

    declare -A execResults

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

            echo "------------------------------------------------------------------------"
            echo "Run command "
            echo "-"
            echo "$cmd"
            echo "-"
            echo "in local repositories of "
            echo "$userdir/."
            echo "------------------------------------------------------------------------"

            cd "$userdir"
            for repo in *; do
                if [ ! -d "$userdir/$repo" ]; then
                    break;
                fi
                latestbranch=""
                cd "$userdir/$repo"
                for branch in main master latest 11.5 11.x 11 10.4 10.x 10 9.5 9.x 9 8.7 8.x 8 7.6 7.x 7; do
                    # Checkout and update current branch
                    exists=$(git branch -a --list "origin/$branch")
                    if [ -n "$exists" ]; then
                        git checkout -B $branch origin/$branch || exitMsg "checkout $branch based on origin/$branch in $repo"
                    else
                        continue
                    fi
                    if [ -z "$latestbranch" ]; then
                        latestbranch="$branch"
                    fi

                    # Collect results
                    if [ $isCmdFile -eq 0 ]; then
                        results=$(eval "$cmd")
                    else
                        results=$(bash "$cmd")
                    fi
                    numResults=$(echo "$results" | wc -l)
                    if [ -n "$results" ] ; then
                        execResults["$host/$user/$repo/$branch|results"]=$results
                        execResults["$host/$user/$repo/$branch|numResults"]=$numResults
                        if [ $stopOnFirstHit -eq 1 ]; then
                            echo "Stopping on first hit."
                            if [ -n "$latestbranch" ]; then
                                git checkout -f $latestbranch
                            fi
                            break 4
                        fi
                    fi
                done
                if [ -n "$latestbranch" ]; then
                    git checkout -f $latestbranch
                fi
            done
        done
    done

    local totalResults=0
    # Sort results by repository name
    mapfile -d '' sorted < <(printf '%s\0' "${!execResults[@]}" | sort -z)
    # Print results
    for x in "${sorted[@]}"; do
        if [[ $x =~ \|results$ ]]; then
            searchPath=${x%|*}
            results=${execResults["$searchPath|results"]}
            numResults=${execResults["$searchPath|numResults"]}
            totalResults=$((totalResults+numResults))
            if [ $quiet -ne 1 ] ; then
                echo "------------------------------------------------------------------------"
                printf "%s: %s result(s)\n" "$searchPath" "$numResults"
                echo "------------------------------------------------------------------------"
                echo "$results"
            else
                printf "%s: %s result(s)\n" "$searchPath" "$numResults"
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

handleRequest "$@"
