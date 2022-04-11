#!/bin/bash

thisdir=$(dirname $(realpath "$0"))
source "$thisdir/config.sh"
source "$thisdir/helpers.sh"

function usage()
{
    echo "Usage: $0 <command> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   command: Execute this command in all branches of all local repositories. This parameter can also be the absolute file path of a bash script."
    echo "   user: Execute the command in the local repositories of this GitHub user namespace (all, $(getUsers 'all' ', ')). Multiple users must be separated by space, e.g. \"friendsoftypo3 typo3\". [default: \"typo3-documentation\"]"
    exit 1
}

if [ $# -lt 1 ] || [ $# -gt 2 ]; then
    usage
fi

cmd="$1"
user="${2:-typo3-documentation}"

users=$(getUsers "$user" " ")
if [ -z "$users" ]; then
    usage
fi

quiet=0
stopOnFirstHit=0
if [ ! -f "$cmd" ]; then
    isCmdFile=0
else
    isCmdFile=1
fi

declare -A execResults

for user in $users; do
    userdir="$repodir/$user"

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
                git checkout -f $branch || exitMsg "checkout $branch in $repo"
                git reset --hard origin/$branch || exitMsg "reset --hard origin/$branch in $repo"
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
                execResults["$user/$repo/$branch|results"]=$results
                execResults["$user/$repo/$branch|numResults"]=$numResults
                if [ $stopOnFirstHit -eq 1 ]; then
                    echo "Stopping on first hit."
                    if [ -n "$latestbranch" ]; then
                        git checkout -f $latestbranch
                    fi
                    break 3
                fi
            fi
        done
        if [ -n "$latestbranch" ]; then
            git checkout -f $latestbranch
        fi
    done
done

totalResults=0
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
