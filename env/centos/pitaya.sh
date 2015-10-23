#!/bin/bash


PITAYA_DIR="${PITAYA_LIB_PATH}";
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



## Check command
mod=$1;

if [ "${mod}" == "-c" ]; then

	## Check Pitaya Library Path
	if [ "${PITAYA_DIR}" == "" ]; then
		echo "PITAYA_LIB_PATH environmental variable is not defined!" | STDERR;
		exit 1;
	fi;


	shift;
	command=$1;
	case "${command}" in
		"create" )
			shift;
			if [ ! -e "./Pitaya" ]; then mkdir Pitaya; fi;
			if [ ! -e "./Services" ]; then mkdir Services; fi;
			if [ ! -e "./Share" ]; then mkdir Share; fi;
			find "${PITAYA_DIR}/env" -type f -not -name "pitaya.sh" -exec cp \{\} ./ \;
			if [ -e "cli.example.php" ]; then mv "cli.example.php" "cli.php"; fi;
			if [ -e "config.example.php" ]; then mv "config.example.php" "config.php"; fi;
			if [ -e "common.example.php" ]; then mv "common.example.php" "common.php"; fi;
			if [ -e "config-host.example.php" ]; then
				if [ "$1" == "--host-base" ] || [ "$1" == "-h" ]; then shift;
					mv "config-host.example.php" "config-host.php";
				else
					rm "config-host.example.php";
				fi;
			fi;

			echo -e "\n\nmount -o bind \"${PITAYA_DIR}/src\" \"./Pitaya\"\n" >> "./.boot.sh";
			exit 0;
		;;

		"unmount" )
			UNMOUNT_PROJ
			exit 0;
		;;

		"destroy" )
			UNMOUNT_PROJ
			find "${WORKING_FOLDER}" -mindepth 1 -maxdepth 1 -not -name "pitaya.sh" -not -name "." -not -name ".." -exec rm -rf \{\} \;
			exit 0;
		;;

		* )
			if [ ! "${command}" == "" ]; then
				echo "${command} is not a valid command!" | STDERR;
			fi;

			echo "Supported commands are create, unmount, and destroy!" | STDERR;

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
