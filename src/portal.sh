#!/bin/bash
SCRIPT_PATH=$(dirname $0);

i=0
for STMT in $@; do ARGS[$i]=$( echo "$STMT" ); (( i++ )); done;
php -d error_reporting=30711 "${SCRIPT_PATH}/portal.php" ${ARGS[@]}
