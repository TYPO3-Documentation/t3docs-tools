#!/bin/bash
# shows all repositories for which a version branch exists

# -------------------
# automatic variables
# -------------------
thisdir=$(dirname $0)
cd $thisdir
curdir=$(pwd)
thisdir=$(pwd)


# config
source $thisdir/config.sh


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
    cd $generateddir

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
