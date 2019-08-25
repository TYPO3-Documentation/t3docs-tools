#!/bin/bash
# shows all repositories for which a version branch exists

curdir=$(pwd)

function usage()
{
    echo "Usage: $0 <version>"
    exit 1
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}

if [ $# -ne 1 ];then
    usage
fi

version=$1

php -f get-repo-names.php | while read i;do
    cd data

    if [ ! -d $i ];then
        echo "$i does not exist, fetch repos first"
        exit 1
    fi

    cd $i

    git branch -a | grep "remotes\/origin\/$version" >/dev/null
    if [ $? -ne 0 ];
        then echo "$i missing version $version"
     fi;
     cd $curdir

done
