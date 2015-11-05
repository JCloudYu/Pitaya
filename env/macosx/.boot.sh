#!/bin/bash

PREV_WD=$(pwd)
cd $(dirname "$0")
CURR_WD=$(pwd)

BOOT_TIME=($(sysctl -a | grep kern.boottime))
IFS=','; BOOT_TIME=( ${BOOT_TIME[4]} ); IFS=' ';
BOOT_TIME=( ${BOOT_TIME[0]} );

if [ -f ".b_flag" ] && [ "${BOOT_TIME}" -lt "$(stat -f "%a" .b_flag 2>&1)" ]; then exit 1; else touch ".b_flag"; fi
