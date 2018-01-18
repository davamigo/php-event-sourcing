#!/bin/bash

dockerfile="docker-compose.yml";

dir=$(dirname $0)
cd $dir

docker-compose -f $dockerfile build --pull --force --no-cache
