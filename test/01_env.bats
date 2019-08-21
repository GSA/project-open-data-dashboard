setup() {
    if which sudo > /dev/null; then
      sudo touch /var/www/.env 
      sudo chmod 666 /var/www/.env
    else
      touch /var/www/.env 
      chmod 666 /var/www/.env 
    fi
}

teardown () {
    if which sudo > /dev/null; then
      sudo /bin/rm -f /var/www/.env
    else
      /bin/rm -f /var/www/.env
    fi
}

@test "Migration should fail when env is empty" {
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME
    php index.php migrate | grep -q "Unable to connect to your database server using the provided settings"
}

@test "Migration should work when proper dotenv is used" {
    cat <<-END >/var/www/.env
DB_HOST=database
DB_USER=root
DB_PASSWORD=mysql
DB_NAME=dashboard
END
    unset DB_HOST DB_USER DB_PASSWORD DB_NAME
    run php index.php migrate
    [ "${lines[0]}" = "The migration was run" ]
    
}