#!/bin/bash

cd $(dirname $0)
php -d auto_prepend_file=kernel/base.php portal.php $@