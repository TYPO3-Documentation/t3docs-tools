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
    echo "Usage: $0 <argument> [<user>]"
    echo ""
    echo "Arguments:"
    echo "   argument: Search for this string in the Documentation/Settings.cfg files of the local repositories."
    echo "   user: Search in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3, friendsoftypo3). [default: \"typo3-documentation\"]"
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

if [ $# -lt 1 ] || [ $# -gt 2 ]; then
    usage
fi

argument="$1"
user="${2:-typo3-documentation}"
if [ "$user" = "all" ]; then
    users="typo3-documentation typo3 friendsoftypo3"
elif [ "$user" = "typo3-documentation" ] || [ "$user" = "typo3" ] || [ "$user" = "friendsoftypo3" ]; then
    users="$user"
else
    usage
fi

stopOnFirstHit=1

for user in $users; do
    userdir="$repodir/$user"

    if [ ! -d "$userdir" ]; then
        exitMsg "The TYPO3 repositories are not pulled to \"$userdir\" yet. Run get-repos.sh first."
    fi

    echo "------------------------------------------------------------------------"
    echo "Search for \"$argument\" in Documentation/Settings.cfg of local repositories of "
    echo "$userdir/."
    echo "------------------------------------------------------------------------"

    cd "$userdir"
    for repo in TYPO3CMS*; do
        if [ ! -d "$userdir/$repo" ]; then
            break;
        fi
        latestbranch=""
        cd "$userdir/$repo"
        for branch in main master latest 11.5 11.x 11 10.4 10.x 10 9.5 9.x 9 8.7 8.x 8 7.6 7.x 7; do
            # Checkout and update current branch
            exists=$(git branch -a --list "origin/$branch")
            if [ -n "$exists" ]; then
                echo "$repo ($branch): Searching .."
                git checkout -f $branch || exitMsg "checkout $branch in $repo"
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
                        git checkout -f $latestbranch
                    fi
                    break 3
                fi
            else
                echo "$repo ($branch): Miss."
            fi
        done
        if [ -n "$latestbranch" ]; then
            git checkout -f $latestbranch
        fi
        echo "------------------------------------------------------------------------"
    done
done
