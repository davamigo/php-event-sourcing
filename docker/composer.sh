#!/bin/bash

user="david"
service="php7.1"
composer="/usr/local/bin/composer"

dir=$(dirname $0)
cd $dir

args=""
while [[ $# -ge 1 ]]
do
    args="$args $1"
    shift
done

docker-compose run --rm -u $user $service $composer $args

