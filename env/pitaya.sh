#!/bin/bash

function ENV_ZONE()
{
	while read CLK_CFG; do
		CFG=(${CLK_CFG//=/ })
		if [ ${CFG[0]} == "ZONE" ]; then echo -n "${CFG[1]}"; fi
	done < "/etc/sysconfig/clock"
}

export ENV_ARGUMENTS="-d error_reporting=30711 -d date.timezone=$(ENV_ZONE)"
./Pitaya/portal.sh $@
