#!/bin/bash

EXT_ARGUMENTS=(${ENV_ARGUMENTS})

cd $(dirname $0)
# E_ALL & ~E_WARNING & ~E_STRICT
php -d auto_prepend_file=kernel/base.php -d error_reporting=30711 ${EXT_ARGUMENTS[@]} portal.php $@
