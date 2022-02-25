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
    echo "Usage: $0 <command> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   command: Execute this search command in all branches of all local repositories."
    echo "   user: Execute the search command in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3, friendsoftypo3). [default: \"typo3-documentation\"]"
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

cmd="$1"
user="${2:-typo3-documentation}"
if [ "$user" = "all" ]; then
    users="typo3-documentation typo3 friendsoftypo3"
elif [ "$user" = "typo3-documentation" ] || [ "$user" = "typo3" ] || [ "$user" = "friendsoftypo3" ]; then
    users="$user"
else
    usage
fi

quiet=0
stopOnFirstHit=0

declare -A searchResults

for user in $users; do
    userdir="$repodir/$user"

    if [ ! -d "$userdir" ]; then
        exitMsg "The TYPO3 repositories are not pulled to \"$userdir\" yet. Run get-repos.sh first."
    fi

    echo "------------------------------------------------------------------------"
    echo "Run search command "
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
        for branch in master main 11.5 11.x 10.4 10.x 9.5 9.x 8.7 8.x 7.6 7.x; do
            # Checkout and update current branch
            exists=$(git branch -a --list "$branch" --list "origin/$branch")
            if [ -n "$exists" ]; then
                git checkout $branch || exitMsg "checkout $branch in $repo"
                git reset --hard origin/$branch || exitMsg "reset --hard origin/$branch in $repo"
            else
                continue
            fi
            if [ -z "$latestbranch" ]; then
                latestbranch="$branch"
            fi

            # Collect search results
            results=$(eval "$cmd")
            numResults=$(echo "$results" | wc -l)
            if [ -n "$results" ] ; then
                searchResults["$user/$repo/$branch|results"]=$results
                searchResults["$user/$repo/$branch|numResults"]=$numResults
                if [ $stopOnFirstHit -eq 1 ]; then
                    echo "Stopping on first hit."
                    if [ -n "$latestbranch" ]; then
                        git checkout $latestbranch
                    fi
                    exit 0
                fi
            fi
        done
        if [ -n "$latestbranch" ]; then
            git checkout $latestbranch
        fi
    done
done

totalResults=0
# Sort search results by repository name
mapfile -d '' sorted < <(printf '%s\0' "${!searchResults[@]}" | sort -z)
# Print search results
for x in "${sorted[@]}"; do
    if [[ $x =~ \|results$ ]]; then
        searchPath=${x%|*}
        results=${searchResults["$searchPath|results"]}
        numResults=${searchResults["$searchPath|numResults"]}
        totalResults=$((totalResults+numResults))
        if [ $quiet -ne 1 ] ; then
            echo "------------------------------------------------------------------------"
            printf "%s: %s search result(s)\n" "$searchPath" "$numResults"
            echo "------------------------------------------------------------------------"
            echo "$results"
        else
            printf "%s: %s search result(s)\n" "$searchPath" "$numResults"
        fi
    fi
done
if [ $totalResults -gt 0 ] ; then
    echo "------------------------------------------------------------------------"
    printf "Total: %s search result(s)\n" "$totalResults"
    echo "------------------------------------------------------------------------"
else
    echo "------------------------------------------------------------------------"
    echo "No search results"
    echo "------------------------------------------------------------------------"
fi
