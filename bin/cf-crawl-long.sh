#!/bin/sh

set -e

# To be executed with the app to use, ie ./cf-crawl-daily.sh dashboard-stage

cf run-task "$1" --command "php public/index.php campaign status long-running full-scan" --name dashboard-long-running-full-scan