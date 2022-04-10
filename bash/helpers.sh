#!/bin/bash

function getUsers()
{
    local user="$1"
    local separator="$2"

    local users=$(php -f $phpdir/get-users.php "$user" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    users=${users//[[:space:]]/$separator}
    echo "$users"
}

function exitMsg()
{
    echo "ERROR: $*"
    exit 1
}
