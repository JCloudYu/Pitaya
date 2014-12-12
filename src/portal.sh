#!/bin/bash

EXT_ARGUMENTS=(${ENV_ARGUMENTS})

cd $(dirname $0)
php -d auto_prepend_file=kernel/base.php ${EXT_ARGUMENTS[@]} portal.php $@
