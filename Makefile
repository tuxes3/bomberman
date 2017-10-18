server:
	php -S localhost:8008 -t web/

websocket:
	php bin/websocket_server.php

install:
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	php composer.phar install
