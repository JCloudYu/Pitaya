#!/bin/bash

## FUNCTIONS
function STDERR { cat - 1>&2; }
###



cd $(dirname "$0");
WORKING_FOLDER=$( pwd );



if [ ! -e "./Pitaya/portal.sh" ]; then
	echo "Current directory is not a valid pitaya project directory!" | STDERR;
	exit 1;
fi;


count=0; while [ "$#" -gt "0" ]; do ARGS[$count]=$( echo "$1" ); shift; (( count++ )); done;
./Pitaya/portal.sh "${ARGS[@]}"
