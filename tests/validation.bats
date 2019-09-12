#! /usr/bin/env bats

# select test environment with
# TARGET=tk bats validation.bats
# or 
# docker-compose exec -e TARGET=dc app bats -r tests/validation.bats
case "${TARGET:-tk}" in
  docker-compose | dc)
    CMD="curl"
    URLROOT="http://localhost"
    ;;
  production)
    CMD="curl"
    URLROOT="https://labs.data.gov/dashboard"
    ;;
  test-kitchen | tk | * )
    DOCKER=$(docker ps -f  'name=dashboardwebubuntu1804' -q)
    CMD="docker exec $DOCKER curl -k"
    URLROOT="https://localhost"
    ;;
esac

setup() {
  if [[ "$BATS_TEST_NUMBER" -eq 1 ]]; then
    echo "# CMD: $CMD URLROOT: $URLROOT" >&3
  fi
}

@test "First test is true to always pass" {
  true
}

@test "Sanity check 200 OK w/ curl of /offices/qa works" {
  $CMD "$URLROOT/offices/qa" --silent --fail |
        grep -q "<title>Project Open Data Dashboard</title>"
}

@test "Sanity check 404 w/ curl of asldjf" {
  $CMD -w "%{http_code}" -s -o /dev/null "$URLROOT/asldfj"  | 
    grep -q '^404$'
}

@test "HTTPS://...cio.gov/catalog-sample should be valid" {
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=https://project-open-data.cio.gov/v1.1/examples/catalog-sample.json
  echo ${lines[4]} | grep -q '"valid_json": true,'
}

@test "HTTP://...cio.gov/catalog-sample should follow redirect and be valid" {
  # Make sure it's still a redirect:
  datajson_url=http://project-open-data.cio.gov/v1.1/examples/catalog-sample.json
  $CMD -w "%{http_code}" -s -o /dev/null $datajson_url | grep -q '^302$'
  # now test the follow
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=$datajson_url
  echo ${lines[4]} | grep -q '"valid_json": true,'
}

@test "HTTPS://...baddata.json should be invalid" {
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=https://raw.githubusercontent.com/GSA/project-open-data-dashboard/pdb/docker-ssrf/tests/baddata.json
  echo ${lines[2]} | grep -q '"valid_json": false,'
}

@test "Upload of opendata.json should be valid" {
  run $CMD -s -F "datajson_upload=@$BATS_TEST_DIRNAME/opendata.json" \
    $URLROOT/validate?schema=federal-v1.1\&output=json
  echo ${lines[4]} | grep -q '"valid_json": true,'
  echo ${lines[5]} | grep -q '"total_records": 3'
}

@test "Upload of baddata.json should be invalid" {
  run $CMD -s -F "datajson_upload=@$BATS_TEST_DIRNAME/baddata.json" \
    $URLROOT/validate?schema=federal-v1.1\&output=json
  echo ${lines[1]} | grep -q '"valid_json": false,'
  echo ${lines[4]} | grep -q '"This does not appear to be valid JSON"'
}

@test "Post of bad json should be invalid" {
  run $CMD -s -F datajson='{ "conformsTo": "foo", "dataset" : [ "bar", "baz" ] }' \
    $URLROOT/validate?schema=federal-v1.1\&output=json
  echo $output | grep -q '"valid": false,'
  echo $output | grep -q '"valid_json": true,'
  echo $output | grep -q '"total_records": 2'
}

# Note: 248 length is the HTTP -> HTTPS redirect message in production
@test "Redir to 127.0.0.1:443 should not be a valid URL" {
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=http://redir.xpoc.pro/127.0.0.1:443
  echo ${lines[5]} | grep -q '"The URL does not appear to be valid"'
}

@test "Redir to 127.0.0.1:442 should not be a valid URL" {
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=http://redir.xpoc.pro/127.0.0.1:442
  echo ${lines[5]} | grep -q '"The URL does not appear to be valid"'
}

@test "Redir to http://169.254.169.254/latest/meta-data/hostname should not be valid" {
  if [ "$TARGET" != "production" ]; then
   : # skip
  fi
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=http://redir.xpoc.pro/169.254.169.254/latest/meta-data/hostname
  echo ${lines[5]} | grep -q '"The URL does not appear to be valid"'
#  echo ${lines[*]: -2:1} | grep -vq '"download_content_length": 37'
}

@test "GET of http://169.254.169.254/latest/meta-data/hostname should not be valid" {
  if [ "$TARGET" != "production" ]; then
    : # skip
  fi
  run $CMD -s \
    $URLROOT/validate?schema=federal-v1.1\&output=json\&datajson_url=http://169.254.169.254/latest/meta-data/hostname
  echo ${lines[5]} | grep -q '"The URL does not appear to be valid"'
}
