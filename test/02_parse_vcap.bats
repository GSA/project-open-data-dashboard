#! /usr/bin/env bats

setup() {
    :>.env
    export VCAP_SERVICES=' 
        { "aws-rds": [{
            "name": "database",
            "credentials": {
              "db_name": "drupal",
              "host": "database",
              "password": "mysql",
              "port": "3306",
              "username": "root"
            }
          }],
          "user-provided": [{
            "name": "secrets",
            "credentials": {
              "ENCRYPTION_KEY": "not-a-good-key-from-vcap-services"
            }
          }]
        }'
}

@test "Bootstrap parses VCAP_SERVICES" {
    run ./bootstrap.sh
    grep -q ENCRYPTION_KEY .env
}