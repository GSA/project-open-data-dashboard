#!/usr/bin/env bats

APP_DIR=/var/www/app

setup(){
    if [[ "$BATS_TEST_NUMBER" -eq 1 ]]; then
        sed -i -E 's/(sess_use_database[^=]+)= TRUE;/\1= FALSE;/' ./application/config/config.php
        php index.php migrate
        if  [ -r .env ]; then
          cp .env .env.pretest
        else
          cp dotenv_sample .env
        fi
    fi
}

teardown() {
    if [[ "${#BATS_TEST_NAMES[@]}" -eq "$BATS_TEST_NUMBER" ]]; then
        sed -i -E 's/(sess_use_database[^=]+)= FALSE;/\1= TRUE;/' ./application/config/config.php
        if [ -r .env.pretest ]; then
          cp .env.pretest .env
        else
          cp dotenv_sample .env
        fi
    fi
}

@test "Migration runs subsequently w short output" {
    run php index.php migrate
    [[ "${lines[0]}" = "The migration was run" ]]
    [[ ${#lines[@]} -eq 1  ]]
}

@test "GET w/ curl of /offices/qa works" {
    curl http://localhost/offices/qa --silent --fail | 
        grep -q "<title>Project Open Data Dashboard</title>"
}

@test "Migration should fail when env is empty" {
    /bin/rm -f $APP_DIR/.env
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME DB_DEBUG
    DB_DEBUG=true php index.php migrate | grep -q "Unable to connect to your database server using the provided settings"
}
