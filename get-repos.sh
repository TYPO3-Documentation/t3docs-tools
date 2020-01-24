#!/bin/bash

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
curdir=$(pwd)
thisdir=$(pwd)


# config
source $thisdir/config.sh

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

mkdir -p $generateddir || exitMsg "Error creating directory $generateddir"

php -f $phpdir/get-repo-names.php | while read i;do
    echo $i
    cd $generateddir
    if [ ! -d $i ];then
        git clone git@github.com:TYPO3-Documentation/$i.git || exitMsg "clone $i"
    fi
    cd $i
    git fetch || exitMsg "fetch $i"
    cd $curdir
done
