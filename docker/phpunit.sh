#!/bin/bash

dockerfile="docker-compose.yml";
user="david"
service="php7.1"
phpunit="./bin/phpunit"
public="../public"

dir=$(dirname $0)
cd $dir

args=""
while [[ $# -ge 1 ]]
do
    args="$args $1"
    shift
done

docker-compose -f $dockerfile run --rm -u $user $service php $phpunit -c tests/phpunit.xml $args
if [ $? -ne 0 ]; then
    exit 1
fi

echo -e "\nCode coverage summary:"
egrep "\s(Classes|Methods|Lines):\s(.+\%)" ../runtime/coverage/coverage.txt | head -3
echo -e ""

if [ -d "$public" ]; then
    cd $public
    if [ ! -e coverage ]; then
        ln -s ../runtime/coverage/
    fi;
fi;
