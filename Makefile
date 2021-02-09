.PHONY: all build clean down install-dependencies install-dev-dependencies integration-tests test unit-tests up update-dependencies

all: clean build install-dev-dependencies up test

build:
	docker-compose build

clean:
	docker-compose down -v --remove-orphans

down:
	docker-compose down

test: build install-dev-dependencies up unit-tests integration-tests

install-dependencies:
	bin/composer install --no-dev

install-dev-dependencies:
	bin/composer install

integration-tests:
	docker-compose exec app bats -r tests/

unit-tests: install-dev-dependencies
	docker-compose exec app composer test

up:
	docker-compose up -d
	docker-compose exec app mkdir -p ./uploads && docker-compose exec app chmod 777 ./uploads
	docker-compose exec app chmod 777 ./archive


update-dependencies:
	bin/composer update
