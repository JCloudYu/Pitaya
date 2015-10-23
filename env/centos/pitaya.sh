#!/bin/bash

cd $(dirname "$0");
WORKING_FOLDER=$( pwd );



## FUNCTIONS
function UNMOUNT_PROJ {
	for DIR in $( find "${WORKING_FOLDER}" -mindepth 1 -maxdepth 1 -type d -not -name "." -not -name ".." ); do
		CANDIDATES=$( mount -l | grep "${DIR}" );
		if [ "${#CANDIDATES[@]}" -eq "0" ]; then continue; fi;

		for CANDIDATE_PATHS in "${CANDIDATES[@]}"; do
			read -a RESULT_COMPS <<< "${CANDIDATE_PATHS}";
			if [ "${RESULT_COMPS[2]}" == "${DIR}" ]; then
				umount ${DIR}
			fi;
		done;
	done;
}

function STDERR { cat - 1>&2; }
###






## Check command
mod=$1;

if [ "${mod}" == "-c" ]; then
	shift;
	command=$1;
	case "${command}" in
		"unmount" )
			UNMOUNT_PROJ
			exit 0;
		;;

		* )
			if [ ! "${command}" == "" ]; then
				echo "${command} is not a valid command!" | STDERR;
			fi;

			echo "Supported commands are unmount" | STDERR;

			exit 1;
		;;
	esac;

	exit 0;
fi

if [ ! -e "./Pitaya/portal.sh" ]; then
	echo "Current directory is not a valid pitaya project directory!" | STDERR;
	exit 1;
fi;


count=0
for STMT in $@; do ARGS[$count]=$( echo "$STMT" ); (( count++ )); done;
./Pitaya/portal.sh ${ARGS[@]}
