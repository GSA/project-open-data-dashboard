APP_DIR=/var/www/app

@test "Migration should fail when env is empty" {
    /bin/rm -f $APP_DIR/.env
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME
    php index.php migrate | grep -q "Unable to connect to your database server using the provided settings"
}

@test "Migration should work when proper dotenv is used" {
    cat <<-END >$APP_DIR/.env
DB_HOST=database
DB_USER=root
DB_PASSWORD=mysql
DB_NAME=dashboard
ENCRYPTION_KEY=this-is-another-fake-key
END
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME
    run php index.php migrate
    [ "${lines[0]}" = "The migration was run" ]
    
}