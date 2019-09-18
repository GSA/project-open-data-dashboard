#!/bin/bash 
set -euo pipefail

fail() {
  echo FAIL: "$@"
  exit 1
}

chmod 755 $APP_DIR/uploads

SECRETS=$(echo $VCAP_SERVICES | jq -r '.["user-provided"][] | select(.name == "secrets") | .credentials') ||
  fail "Unable to parse SECRETS from VCAP_SERVICES"
ENCRYPTION_KEY=$(echo $SECRETS | jq --exit-status -r '.ENCRYPTION_KEY') ||
  fail "Unable to parse ENCRYPTION_KEY from SECRETS"

DB_NAME=$(echo $VCAP_SERVICES | jq -r '.["aws-rds"][] | .credentials.db_name')
DB_USER=$(echo $VCAP_SERVICES | jq -r '.["aws-rds"][] | .credentials.username')
DB_PASSWORD=$(echo $VCAP_SERVICES | jq -r '.["aws-rds"][] | .credentials.password')
DB_HOST=$(echo $VCAP_SERVICES | jq -r '.["aws-rds"][] | .credentials.host')
DB_PORT=$(echo $VCAP_SERVICES | jq -r '.["aws-rds"][] | .credentials.port')

:>$APP_DIR/.env
for e in DB_NAME DB_USER DB_PASSWORD DB_HOST DB_PORT ENCRYPTION_KEY; do 
  echo "$e=${!e}" >> $APP_DIR/.env
done 

which apache2-foreground && exec "apache2-foreground" || exit 0
