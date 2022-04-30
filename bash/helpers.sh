#!/bin/bash

function getHosts()
{
    local host="$1"
    local separator="$2"

    local hosts
    hosts=$(php -f $phpdir/get-hosts.php "$host" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    hosts=$(echo $hosts | sed "s/[[:space:]]/$separator/g")
    echo "$hosts"
}

function getHostsConfig()
{
    local host="$1"

    local config
    config=$(php -f $phpdir/get-hosts-config.php "$host")
    echo "$config"
}

function getUsers()
{
    local host="$1"
    local user="$2"
    local separator="$3"

    local users
    users=$(php -f $phpdir/get-users.php "$host" "$user" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    users=$(echo $users | sed "s/[[:space:]]/$separator/g")
    echo "$users"
}

function getRepoNames()
{
    local type="$1"
    local host="$2"
    local user="$3"
    local token="$4"
    local force="$5"
    local separator="$6"

    local repos
    repos=$(php -f $phpdir/get-repo-names.php "$type" "$host" "$user" "$token" "$force" | tr '\n' ' ' | sed -e 's/[[:space:]]*$//')
    repos=$(echo $repos | sed "s/[[:space:]]/$separator/g")
    echo "$repos"
}

function exitMsg()
{
    echo "ERROR: $@"
    exit 1
}
