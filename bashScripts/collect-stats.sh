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
    echo "Usage: $0 [<type>] [<user>]"
    echo ""
    echo "Arguments:"
    echo "   type: Collect the statistics of all repositories or only of those starting with \"TYPO3CMS-\" (all, docs). [default: \"docs\"]"
    echo "   user: Collect the statistics in the local repositories of this GitHub user namespace (all, typo3-documentation, typo3, friendsoftypo3). [default: \"typo3-documentation\"]"
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

if [ $# -gt 2 ]; then
    usage
fi

type="${1:-docs}"
user="${2:-typo3-documentation}"
if [ "$user" = "all" ]; then
    users="typo3-documentation typo3 friendsoftypo3"
elif [ "$user" = "typo3-documentation" ] || [ "$user" = "typo3" ] || [ "$user" = "friendsoftypo3" ]; then
    users="$user"
else
    usage
fi

declare -A screenshots

for user in $users; do
    userdir="$repodir/$user"

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

            # Collect automatic screenshots statistics
            if echo "master main 11.5" | grep -w "$branch"; then
                documentationFolders=$(find . -type d -name "Documentation")
                for documentationFolder in $documentationFolders; do
                    numImages=$(find "$documentationFolder" -type f \( -iname "*.png" -or -iname "*.jpg" -or -iname "*.jpeg" -or -iname "*.gif" -or -iname "*.webp" \) | wc -l)
                    numAutomatic=0
                    automaticFolders=$(find "$documentationFolder" -type d -name "AutomaticScreenshots")
                    for automaticFolder in $automaticFolders; do
                        n=$(find "$automaticFolder" -type f \( -iname "*.png" -or -iname "*.jpg" -or -iname "*.jpeg" -or -iname "*.gif" -or -iname "*.webp" \) | wc -l)
                        numAutomatic=$((numAutomatic+n))
                    done
                    screenshots["$user/$repo/$branch:$documentationFolder|all"]=$numImages
                    screenshots["$user/$repo/$branch:$documentationFolder|automatic"]=$numAutomatic
                done
            fi
        done
        if [ -n "$latestbranch" ]; then
            git checkout $latestbranch
        fi
    done
done

echo "------------------------------------------------------------------------"
echo "Automatic screenshots"
echo "------------------------------------------------------------------------"
totalImages=0
totalAutomatic=0
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
totalPercentage=0
if [ "$totalImages" -gt 0 ]; then
    totalPercentage=$(echo "result=$totalAutomatic/$totalImages*100;scale=2;result/1" | bc -l)
fi
echo "------------------------------------------------------------------------"
printf "Total = %s (automatic) / %s (all) = %s%%\n" "$totalAutomatic" "$totalImages" "$totalPercentage"
echo "------------------------------------------------------------------------"
