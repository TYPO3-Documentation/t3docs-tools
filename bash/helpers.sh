#!/bin/bash

function getUsers()
{
    local user="$1"
    local separator="$2"

    local users
    users=$(php -f $phpdir/get-users.php "$user" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    users=${users//[[:space:]]/$separator}
    echo "$users"
}

function getRepoNames()
{
    local type="$1"
    local user="$2"
    local token="$3"
    local separator="$4"

    local repos
    repos=$(php -f $phpdir/get-repo-names.php "$type" "$user" "$token" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    repos=${repos//[[:space:]]/$separator}
    echo "$repos"
}

function exitMsg()
{
    echo "ERROR: $@"
    exit 1
}
