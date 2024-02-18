init: docker-down-clear docker-build docker-up composer-init
up: docker-up
down: docker-down
restart: down up

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-build:
	docker-compose build

composer-init:
	docker-compose run --rm php composer install

run-script:
	docker-compose run --rm php php public/index.php