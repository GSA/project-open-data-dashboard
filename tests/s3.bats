#! /usr/bin/env bats


case "${TARGET:-dc}" in
  docker-compose | dc)
    CMD="curl"
    URLROOT="http://localhost"
    ;;
  production|prod)
    CMD="curl"
    URLROOT="https://labs.data.gov/dashboard"
    ;;
  test-kitchen | tk)
    DOCKER=$(docker ps -f  'name=dashboardwebubuntu1804' -q)
    CMD="docker exec $DOCKER curl -k"
    URLROOT="https://localhost"
    ;;
  cloud-gov | cg)
    ROUTE=$(cf apps | awk '/^app/ { print $NF }')
    CMD="curl"
    URLROOT="https://$ROUTE"
    ;;
esac

ARCHIVE=https://s3.amazonaws.com/bsp-ocsit-prod-east-appdata/datagov/dashboard/archive/datajson/2018-08-31/49015.json
URLENCODED_ARCHIVE="https%3A%2F%2Fs3.amazonaws.com%2Fbsp-ocsit-prod-east-appdata%2Fdatagov%2Fdashboard%2Farchive%2Fdatajson%2F2018-08-31%2F49015.json"

@test "Create valid URLS to existing archive" {
    run $CMD -s \
      $URLROOT/offices/detail/49015/2018-08-31
    echo ${output} | grep -q $URLENCODED_ARCHIVE
}