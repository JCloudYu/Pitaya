#!/bin/bash

BOOT_TIME=($(cat /proc/stat | grep btime))
BOOT_TIME=${BOOT_TIME[1]}

if [ -f ".b_flag" ] && [ $BOOT_TIME -lt "$(stat -c %Y .t-boot 2>&1)" ]; then exit 0; else touch ".b_flag"; fi
