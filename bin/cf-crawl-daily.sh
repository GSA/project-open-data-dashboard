#!/bin/sh

set -e

# To be executed with the app to use, ie ./cf-crawl-daily.sh dashboard-stage

cf run-task "$1" --command "php public/index.php campaign status omb-monitored download" --wait --name dashboard-omb-monitored-download

cf run-task "$1" --command "php public/index.php campaign status omb-monitored full-scan" --wait --name dashboard-omb-monitored-full-scan