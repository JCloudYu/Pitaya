#!/bin/bash

cd $( dirname "$0" )

i=0
for STMT in $@; do
    ARGS[$i]=$( echo "$STMT" )
    (( i++ ))
done

./Pitaya/portal.sh ${ARGS[@]}
