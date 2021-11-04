#!/bin/bash
set -e

echo Running setup...

/var/www/app/docker/wait_for_db && /var/www/app/.profile

exec "$@"
