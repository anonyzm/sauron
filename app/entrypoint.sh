#!/bin/bash
set +u 

case "$APPLICATION" in
"api")
    ;;
"operator")
    SUPERVISOR_CONF_FILE=/app/operator/supervisor.conf
    ;;
"persister")
    SUPERVISOR_CONF_FILE=/app/persister/supervisor.conf
    ;;
"scheduler")
    CRONTAB_CONF_FILE=/app/scheduler/crontab/crontab
    ;;
*)    
    ;;
esac

if [[ ! -z "$SUPERVISOR_CONF_FILE" ]]; then
  cp -f ${SUPERVISOR_CONF_FILE} ${SUPERVISOR_SOURCE_FILE}
fi

if [[ ! -z "$CRONTAB_CONF_FILE" ]]; then
  cp -f ${CRONTAB_CONF_FILE} ${CRONTAB_SOURCE_FILE}
fi

set -u 
