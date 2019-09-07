#!/usr/bin/env bats

APP_DIR=/var/www/app

setup(){
    cp dotenv_sample .env
    sed -i -E 's/(sess_use_database[^=]+)= TRUE;/\1= FALSE;/' ./application/config/config.php
}

teardown() {
    sed -i -E 's/(sess_use_database[^=]+)= FALSE;/\1= TRUE;/' ./application/config/config.php
}

@test "Migration should fail when env is empty" {
    /bin/rm -f $APP_DIR/.env
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME DB_DEBUG
    php index.php migrate | grep -q "Unable to connect to your database server using the provided settings"
}

@test "Migration runs initially w long output" {
    run php index.php migrate
    [[ "${lines[0]}" = "The migration was run" ]]
    [[ "${#lines[@]}" -gt 12 ]]
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

