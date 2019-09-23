#!/bin/bash 
set -euo pipefail

fail() {
  echo FAIL: "$@"
  exit 1
}

mkdir -p $APP_DIR/uploads
chmod 777 $APP_DIR/uploads

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

if echo "$VCAP_APPLICATION" | jq --exit-status '.uris[0]' > /dev/null
then 
  # use defaults for cloud foundry
  uri=$(echo "$VCAP_APPLICATION" | jq -r '.uris[0]')
  echo "DEFAULT_HOST=$uri" >> $APP_DIR/.env
  echo "CONTENT_PROTOCOL=https" >> $APP_DIR/.env
  echo "PROJECT_SHARED_PATH=$APP_DIR" >> $APP_DIR/.env
  echo "USE_LOCAL_STORAGE=true" >> $APP_DIR/.env
fi

which apache2-foreground && exec "apache2-foreground" || exit 0
