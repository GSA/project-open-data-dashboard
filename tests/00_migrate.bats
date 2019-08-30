#!/usr/bin/env bats

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

