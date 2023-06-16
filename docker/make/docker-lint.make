lint:
	docker-compose exec php-fpm sh -c "vendor/bin/phpstan analyse -c phpstan.neon"
.PHONY: docker-db-ok