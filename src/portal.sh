#!/bin/bash

cd $(dirname $0)
# E_ALL & ~E_WARNING & ~E_STRICT

i=0
for STMT in $@; do
    ARGS[$i]=$( echo "$STMT" )
    (( i++ ))
done

php -d error_reporting=30711 portal.php ${ARGS[@]}
