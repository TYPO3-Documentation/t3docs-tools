#!/bin/bash

# automatic variables
thisdir=$(dirname $0)
cd $thisdir
curdir=$(pwd)


# config
datadir=$curdir/data
phpdir=$curdir/


function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

mkdir -p $datadir || exitMsg "Error creating directory $datadir"

php -f $phpdir/get-repo-names.php | while read i;do
    echo $i
    cd data
    if [ ! -d $i ];then
        git clone git@github.com:TYPO3-Documentation/$i.git || exitMsg "clone $i"
    fi
    cd $i
    git fetch || exitMsg "fetch $i"
    cd $curdir
done