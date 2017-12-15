server:
	php -S localhost:8008 -t web/

websocket:
	php bin/websocket_server.php

install:
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	php composer.phar install

docker_build:
	docker build -t bomberman .

docker_run:
	docker kill bomberman || true
	sleep 2
	docker run --rm -p 127.0.0.1:8080:80 --name bomberman -t bomberman
