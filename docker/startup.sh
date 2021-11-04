#!/bin/bash
set -e

/var/www/app/docker/wait_for_db && /var/www/app/.profile && cat
