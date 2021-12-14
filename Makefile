.PHONY: all build clean down install-dependencies install-dev-dependencies integration-tests test unit-tests up update-dependencies

all: clean build install-dev-dependencies up test

build:
	docker-compose build

cf-crawl-save-staging: cf run-task dashboard-stage --command "php public/index.php campaign status omb-monitored download" --wait --name dashboard-omb-monitored-download

cf-crawl-daily-staging: cf run-task dashboard-stage --command "php public/index.php campaign status omb-monitored full-scan" --wait --name dashboard-omb-monitored-full-scan

cf-crawl-long-staging: cf run-task dashboard-stage --command "php public/index.php campaign status long-running full-scan" --name dashboard-long-running-full-scan

cf-crawl-save: cf run-task dashboard --command "php public/index.php campaign status omb-monitored download" --wait --name dashboard-omb-monitored-download

cf-crawl-daily: cf run-task dashboard --command "php public/index.php campaign status omb-monitored full-scan" --wait --name dashboard-omb-monitored-full-scan

cf-crawl-long: cf run-task dashboard --command "php public/index.php campaign status long-running full-scan" --name dashboard-long-running-full-scan

cloud-test: build install-dev-dependencies up test

clean:
	docker-compose down -v --remove-orphans

down:
	docker-compose down

test: unit-tests integration-tests

install-dependencies:
	bin/composer install --no-dev

install-dev-dependencies:
	bin/composer install

integration-tests:
	docker-compose exec -T app bats -r tests/

unit-tests: install-dev-dependencies
	docker-compose exec -T app composer test

up:
	docker-compose up -d
	@for i in `seq 1 10`; do docker-compose exec -T app true && exit 0; echo "Waiting for docker ready..."; sleep 2; done; exit 1
	@docker-compose exec -T app docker/wait_for_db
	docker-compose exec -T app mkdir -p ./uploads && docker-compose exec -T app chmod 777 ./uploads
	docker-compose exec -T app chmod 777 ./archive

update-dependencies:
	bin/composer update
