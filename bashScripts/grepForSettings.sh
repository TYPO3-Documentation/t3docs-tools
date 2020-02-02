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
	exit 1
}

function grepInSettings()
{
	grep "$argument" Documentation/Settings.cfg
}

function log()
{
	msg="$1"
}

if [ $# -ne 1 ];then
	usage
fi

argument="$1"

cd $repodir
for repo in TYPO3CMS*;do
	echo "$repodir,$repo"
	cd $repodir/$repo
	for branch in master 9.5 8.7 7.6 6.2;do
		log "repo=$repo, check for branch=$branch"
		log "check for local branch"
		exists=0;
		git show-ref --verify --quiet refs/heads/$branch
		if [ $? -ne 0 ];then
			log "check for remote branch: $branch"
			git ls-remote -q --exit-code --heads origin $branch
			if [ $? -eq 0 ];then
				exists=1
				git checkout $branch
			fi
		else
			log "local branch exists: $repo $branch"
			exists=1
			git checkout $branch
		fi
		if [ $exists -ne 1 ];then
			log "branch $branch does not exist, continue"
			continue
		fi
		currentbranch=$(git rev-parse --abbrev-ref HEAD)
		if [[ $currentbranch != $branch ]];then
			echo "ERROR: $repo: current branch ($currentbranch) is not branch ($branch)"
			exit 1
		fi
		grepInSettings "$argument"
		if [ $? -eq 0 ];then
			echo "grep found in $repo $branch ... abort"
			exit 0
		fi
        done

done
	
