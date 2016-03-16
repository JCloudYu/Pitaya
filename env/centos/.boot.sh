#!/bin/bash

PREV_WD=$(pwd)
cd $(dirname "$0")
CURR_WD=$(pwd)

REF_TIME=$( date +%s )
BOOT_TIME=( $(cat /proc/uptime) )
IFS='.'; BOOT_TIME=( ${BOOT_TIME[0]} ); IFS=' ';
let "BOOT_TIME=REF_TIME-BOOT_TIME";

if [ -f ".b_flag" ] && [ "${BOOT_TIME}" -lt "$(stat -c %Y .b_flag 2>&1)" ]; then exit 1; else touch ".b_flag"; fi
