# Makefile - skróty do częstych komend

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose up -d --build

shell:
	docker-compose exec app bash

artisan:
	docker-compose exec app php artisan $(cmd)

composer:
	docker-compose exec app composer $(cmd)

logs:
	docker-compose logs -f app

setup:
	cp .env.example .env
	docker-compose up -d --build
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate

migrate:
	docker-compose exec app php artisan migrate

fresh:
	docker-compose exec app php artisan migrate:fresh --seed

tinker:
	docker-compose exec app php artisan tinker