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
	echo "Usage: $0 <command>"
    echo ""
    echo "Arguments:"
    echo "   command: Execute this search command in all branches of all local repositories."
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

if [ $# -ne 1 ]; then
	usage
fi

if [ ! -d "$repodir" ]; then
    exitMsg "The TYPO3 documentation repositories are not pulled to \"$repodir\" yet. Run get-repos.sh first."
fi

cmd="$1"

quiet=0
stopOnFirstHit=0

echo "Run search command "
echo "-"
echo "$cmd"
echo "-"
echo "in local repositories of "
echo "$repodir/."
echo "------------------------------------------------------------------------"

cd "$repodir"
for repo in *; do
	latestbranch=""
	cd "$repodir/$repo"
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
