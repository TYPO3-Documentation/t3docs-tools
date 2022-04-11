#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
thisdir=$(pwd)

source $thisdir/config.sh
source $thisdir/helpers.sh

function usage()
{
    echo "Usage: $0 [<type>] [<user>] [<token>]"
    echo ""
    echo "Arguments:"
    echo "   type: Fetch all repositories or only those starting with \"TYPO3CMS-\" (all, docs). [default: \"all\"]"
    echo "   user: Fetch the repositories of this GitHub user namespace (all, $(getUsers 'all' ', ')), which has to be defined in the /config.yml or /config.local.yml. Multiple users must be separated by space, e.g. \"friendsoftypo3 typo3\". [default: \"typo3-documentation\"]"
    echo "   token: Fetch the repositories using this GitHub API token to overcome GitHub rate limitations. [default: \"\"]"
    exit 1
}

if [ $# -gt 3 ]; then
    usage
fi

type="${1:-all}"
user="${2:-typo3-documentation}"
token="${3:-}"

if [ "$type" != "all" ] && [ "$type" != "docs" ]; then
    usage
fi

users=$(getUsers "$user" " ")
if [ -z "$users" ]; then
    usage
fi

for user in $users; do
    userdir="$repodir/$user"

    if ! mkdir -p "$userdir"; then
        exitMsg "Error creating directory \"$userdir\"."
    fi

    echo "Clone or update local repositories of"
    echo "$userdir/."
    echo "------------------------------------------------------------------------"

    php -f $phpdir/get-repo-names.php "$type" "$user" "$token" | while read repo; do
        cd "$userdir"
        if [ ! -d "$repo" ]; then
            echo "Cloning repo $repo."
            git clone "git@github.com:$user/$repo.git" || exitMsg "clone $repo"
        else
            echo "$repo already exists: Update remote tracking branches, checkout and update main branch."
            cd "$repo"
            # Update remote tracking branches
            git fetch --prune || exitMsg "fetch $repo"
            # Checkout and update main branch
            mainbranch=""
            for branch in main master latest; do
                exists=$(git branch -a --list "origin/$branch")
                if [ -n "$exists" ]; then
                    mainbranch="$branch"
                    break
                fi
            done
            if [ -n "$mainbranch" ]; then
                git checkout -f $mainbranch || exitMsg "checkout $mainbranch in $repo"
                git reset --hard origin/$mainbranch || exitMsg "reset --hard origin/$mainbranch in $repo"
            else
                echo "The $repo repo is not yet initialized because it lacks a main branch."
            fi
        fi
        echo "------------------------------------------------------------------------"
    done
done
