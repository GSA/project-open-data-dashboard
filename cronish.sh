#!/bin/bash 
set -euo pipefail

cd $APP_DIR

while true
do
  echo "Sleeping to let application stablize"
  sleep 30
  echo "Starting crawl"
  php index.php campaign status cfo-act download
  echo 
  echo "Sleeping 23h"
  sleep 23h
done
