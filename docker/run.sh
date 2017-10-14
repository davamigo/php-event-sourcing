#!/bin/bash

user="david"
service="php7.1"

dir=$(dirname $0)
cd $dir

args=""
while [[ $# -ge 1 ]]
do
    args="$args $1"
    shift
done

if [ "$args" == "" ]
then
    docker-compose run --rm -u $user $service bash
else
    docker-compose run --rm -u $user $service $args
fi

