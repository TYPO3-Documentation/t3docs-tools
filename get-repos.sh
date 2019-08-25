#!/bin/bash

curdir=$(pwd)

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

php -f get-repo-names.php | while read i;do
    echo $i
    cd data
    if [ ! -d $i ];then
        git clone git@github.com:TYPO3-Documentation/$i.git || exitMsg "clone $i"
    fi
    cd $i
    git fetch || exitMsg "fetch $i"
     cd $curdir
done