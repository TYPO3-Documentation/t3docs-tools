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
    echo "Usage: $0 [<type>]"
    echo ""
    echo "Arguments:"
    echo "   type: Fetch all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"all\"]"
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

type="${1:-all}"
if [ "$type" != "all" ] && [ "$type" != "docs" ]; then
    usage
fi

if ! mkdir -p "$repodir"; then
    exitMsg "Error creating directory \"$repodir\"."
fi

echo "Clone or update local repositories of"
echo "$repodir/."
echo "------------------------------------------------------------------------"

php -f $phpdir/get-repo-names.php $type | while read repo; do
    cd "$repodir"
    if [ ! -d "$repo" ]; then
        echo "Cloning repo $repo."
        git clone git@github.com:TYPO3-Documentation/$repo.git || exitMsg "clone $repo"
    else
        echo "$repo already exists: Update remote tracking branches, checkout and update main branch."
        cd "$repo"
        # Update remote tracking branches
        git fetch || exitMsg "fetch $repo"
        # Checkout and update main branch
        mainbranch=""
        for branch in master main; do
            exists=$(git branch -a --list "$branch" --list "origin/$branch")
            if [ -n "$exists" ]; then
                mainbranch="$branch"
                break
            fi
        done
        if [ -n "$mainbranch" ]; then
            git checkout $mainbranch || exitMsg "checkout $mainbranch in $repo"
            git reset --hard origin/$mainbranch || exitMsg "reset --hard origin/$mainbranch in $repo"
        else
            echo "The $repo repo is not yet initialized because it lacks a main branch."
        fi
    fi
    echo "------------------------------------------------------------------------"
done
