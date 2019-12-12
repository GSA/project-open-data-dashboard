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

# Truncate the .env file if it already exists
:>$APP_DIR/.env
for e in DB_NAME DB_USER DB_PASSWORD DB_HOST DB_PORT ENCRYPTION_KEY; do
  echo "$e=${!e}" >> $APP_DIR/.env
done

S3_PREFIX=
S3_BUCKET=$(echo $VCAP_SERVICES | jq -r '.["s3"][] | .credentials.bucket')
S3_ACCESS_KEY_ID=$(echo $VCAP_SERVICES | jq -r '.["s3"][] | .credentials.access_key_id')
S3_SECRET_ACCESS_KEY=$(echo $VCAP_SERVICES | jq -r '.["s3"][] | .credentials.secret_access_key')

for e in S3_BUCKET S3_PREFIX S3_ACCESS_KEY_ID S3_SECRET_ACCESS_KEY; do
  echo "$e=${!e}" >> $APP_DIR/.env
done

uri=$(echo "$VCAP_APPLICATION" | jq -r '.uris[0]')
echo "DEFAULT_HOST=$uri" >> $APP_DIR/.env

fake=$(echo "$VCAP_APPLICATION" | jq -r '.fake')
if [ "$fake" = "yes" ]; then
  echo "CONTENT_PROTOCOL=http" >> $APP_DIR/.env
else
  echo "CONTENT_PROTOCOL=https" >> $APP_DIR/.env
fi


#cat<<EOF>>$APP_DIR/.env
echo "PROJECT_SHARED_PATH=$APP_DIR" >> $APP_DIR/.env
echo "USE_LOCAL_STORAGE=true" >> $APP_DIR/.env

echo "ENVIRONMENT=development" >> $APP_DIR/.env

# migrations are idempotent, so run on startup
cd $APP_DIR && php index.php migrate

which apache2-foreground && exec "apache2-foreground" || exit 0
