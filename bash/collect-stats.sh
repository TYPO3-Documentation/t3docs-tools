#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 [<type>] [<host>] [<user>]"
    echo ""
    echo "Arguments:"
    echo "   type: Collect the statistics of all repositories or only of those starting with \"TYPO3CMS-\" (all, docs). [default: \"all\"]"
    echo "   host: Collect the statistics in the local repositories of this host (all, $(getHosts 'all' ', ')). Multiple hosts must be separated by space, e.g. \"github.com gitlab.com\". [default: \"all\"]"
    echo "   user: Collect the statistics in the local repositories of this user namespace (all, $(getUsers 'all' 'all' ', ')). Multiple users must be separated by space, e.g. \"github.com:friendsoftypo3 github.com:typo3\". [default: \"all\"]"
    exit 1
}

function handleRequest()
{
    if [ $# -le 3 ]; then
        collectStats "$@"
    else
        usage
    fi
}

function collectStats()
{
    local type="${1:-all}"
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

    declare -A screenshots

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
            echo "Collect stats command in local repositories of type \"$type\" of "
            echo "$userdir/."
            echo "------------------------------------------------------------------------"

            cd "$userdir"
            for repo in *; do
                if [ ! -d "$userdir/$repo" ]; then
                    break;
                fi
                if [ "$type" = "docs" ] && [[ ! $repo =~ ^TYPO3CMS\- ]]; then
                    continue;
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

                    # Collect automatic screenshots statistics
                    if echo "main master latest 11.5 11.x 11" | grep -w "$branch"; then
                        documentationFolders=$(find . -type d -name "Documentation")
                        for documentationFolder in $documentationFolders; do
                            numImages=$(find "$documentationFolder" -type f \( -iname "*.png" -or -iname "*.jpg" -or -iname "*.jpeg" -or -iname "*.gif" -or -iname "*.webp" \) | wc -l)
                            numAutomatic=0
                            automaticFolders=$(find "$documentationFolder" -type d -name "AutomaticScreenshots")
                            for automaticFolder in $automaticFolders; do
                                n=$(find "$automaticFolder" -type f \( -iname "*.png" -or -iname "*.jpg" -or -iname "*.jpeg" -or -iname "*.gif" -or -iname "*.webp" \) | wc -l)
                                numAutomatic=$((numAutomatic+n))
                            done
                            screenshots["$host/$user/$repo/$branch:$documentationFolder|all"]=$numImages
                            screenshots["$host/$user/$repo/$branch:$documentationFolder|automatic"]=$numAutomatic
                        done
                    fi
                done
                if [ -n "$latestbranch" ]; then
                    git checkout -f $latestbranch
                fi
            done
        done
    done

    echo "------------------------------------------------------------------------"
    echo "Automatic screenshots"
    echo "------------------------------------------------------------------------"
    local totalImages=0
    local totalAutomatic=0
    # Sort statistics by repository name
    mapfile -d '' sorted < <(printf '%s\0' "${!screenshots[@]}" | sort -z)
    # Print statistics
    for x in "${sorted[@]}"; do
        if [[ $x =~ \|all$ ]]; then
            documentationPath=${x%|*}
            numImages=${screenshots["$documentationPath|all"]}
            numAutomatic=${screenshots["$documentationPath|automatic"]}
            if [ "$numImages" -gt 0 ]; then
                totalImages=$((totalImages+numImages))
                totalAutomatic=$((totalAutomatic+numAutomatic))
                percentage=$(echo "result=$numAutomatic/$numImages*100;scale=2;result/1" | bc -l)
                printf "[%s] = %s (automatic) / %s (all) = %s%%\n" "$documentationPath" "$numAutomatic" "$numImages" "$percentage"
            fi
        fi
    done
    local totalPercentage=0
    if [ "$totalImages" -gt 0 ]; then
        totalPercentage=$(echo "result=$totalAutomatic/$totalImages*100;scale=2;result/1" | bc -l)
    fi
    echo "------------------------------------------------------------------------"
    printf "Total = %s (automatic) / %s (all) = %s%%\n" "$totalAutomatic" "$totalImages" "$totalPercentage"
    echo "------------------------------------------------------------------------"
}

handleRequest "$@"
