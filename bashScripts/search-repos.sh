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
    echo "   user: Execute the search command in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3). [default: \"typo3-documentation\"]"
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
    users="typo3-documentation typo3"
elif [ "$user" = "typo3-documentation" ] || [ "$user" = "typo3" ]; then
    users="$user"
else
    usage
fi

quiet=0
stopOnFirstHit=0

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
        for branch in master main 11.5 10.4 9.5 8.7 7.6; do
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
            # Search
            result=$(eval "$cmd")
            resultNum=$(echo "$result" | wc -l)
            if [ -n "$result" ] ; then
                echo "------------------------------------------------------------------------"
                echo "$repo ($branch): $resultNum search results."
                if [ $quiet -ne 1 ] ; then
                    echo "------------------------------------------------------------------------"
                    echo "$result"
                fi
                echo "------------------------------------------------------------------------"
                if [ $stopOnFirstHit -eq 1 ]; then
                    echo "Stopping on first hit."
                    if [ -n "$latestbranch" ]; then
                        git checkout $latestbranch
                    fi
                    exit 0
                fi
            else
                echo "$repo ($branch): No search results."
            fi
        done
        if [ -n "$latestbranch" ]; then
            git checkout $latestbranch
        fi
    done
done
