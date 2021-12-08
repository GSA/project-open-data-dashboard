#!/bin/sh

set -e # exit on any error

api_server=${api:-https://api.fr.cloud.gov}
cf api "$api_server"

if [ -z "$user" ] || [ -z "$password" ]; then
    echo "Environment must contain 'user' and 'password' variables."
    exit 1
else
    cf auth "$user" "$password"
fi

target_args=""
if [ -n "$org" ]; then
    echo "cloud.gov org was specified"
    target_args="-o $org"
fi
if [ -n "$space" ]; then
    echo "cloud.gov space was specified"
    target_args="${target_args} -s $space"
fi
if [ -n "$target_args" ]; then
    echo "Setting target org and space as specified"
    cf target $target_args 
fi