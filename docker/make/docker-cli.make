.PHONY: nginx-cli php-cli

nginx-cli:
	$(info Enter nginx service console)
	docker-compose exec nginx sh

php-cli: 
	$(info Enter php-fpm service console)
	docker-compose exec php-fpm sh
