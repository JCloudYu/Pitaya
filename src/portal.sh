#!/bin/bash
SCRIPT_PATH=$(dirname $0);

count=0; while [ "$#" -gt "0" ]; do ARGS[$count]=$( echo "$1" ); shift; (( count++ )); done;
php -d error_reporting=30711 "${SCRIPT_PATH}/portal.php" "${ARGS[@]}"
