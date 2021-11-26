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
	echo "Usage: $0 <argument>"
    echo ""
    echo "Arguments:"
    echo "   argument: Search for this string in the Documentation/Settings.cfg files of the local repositories."
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

function log()
{
	msg="$1"
}

if [ $# -ne 1 ]; then
	usage
fi

if [ ! -d "$repodir" ]; then
    exitMsg "The TYPO3 documentation repositories are not pulled to \"$repodir\" yet. Run get-repos.sh first."
fi

argument="$1"

stopOnFirstHit=1

echo "Search for \"$argument\" in Documentation/Settings.cfg of local repositories of "
echo "$repodir/."
echo "------------------------------------------------------------------------"

cd "$repodir"
for repo in TYPO3CMS*; do
    latestbranch=""
	cd "$repodir/$repo"
	for branch in master main 11.5 10.4 9.5 8.7 7.6; do
	    # Checkout and update current branch
	    exists=$(git branch -a --list "$branch" --list "origin/$branch")
	    if [ -n "$exists" ]; then
            echo "$repo ($branch): Searching .."
            git checkout $branch || exitMsg "checkout $branch in $repo"
            git reset --hard origin/$branch || exitMsg "reset --hard origin/$branch in $repo"
        else
            continue
        fi
		if [ -z "$latestbranch" ]; then
            latestbranch="$branch"
        fi
        # Search
		grep "$argument" Documentation/Settings.cfg
		if [ $? -eq 0 ]; then
            echo "$repo ($branch): Word \"$argument\" found in Documentation/Settings.cfg."
		    if [ $stopOnFirstHit -eq 1 ]; then
                echo "Stopping on first hit."
                if [ -n "$latestbranch" ]; then
                    git checkout $latestbranch
                fi
                exit 0
            fi
		else
		    echo "$repo ($branch): Miss."
		fi
    done
    if [ -n "$latestbranch" ]; then
        git checkout $latestbranch
    fi
    echo "------------------------------------------------------------------------"
done
	
