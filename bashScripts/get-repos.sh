#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
thisdir=$(pwd)


# config
source $thisdir/config.sh

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

mkdir -p $repodir || exitMsg "Error creating directory $repodir"

php -f $phpdir/get-repo-names.php | while read i;do
    echo "Getting repo $i ..."
    cd $repodir
    if [ ! -d $i ];then
        git clone git@github.com:TYPO3-Documentation/$i.git || exitMsg "clone $i"
    else
        echo "$i already exists, get latest version"
        cd $i
        git checkout master || exitMsg "checkout master in $i"
        git reset --hard origin/master || exitMsg "fetch reset --hard origin/master in $i"
        git pull origin master || exitMsg "fetch pull origin master in $i"
        cd ..
    fi
    cd $i
    git fetch || exitMsg "fetch $i"
    cd $thisdir
    echo "-------------------------"
    echo " "
done
