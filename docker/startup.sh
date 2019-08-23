#!/bin/bash
set -e

/var/www/app/docker/wait_for_db_then /var/www/app/bootstrap.sh
