## For Developement Testing 

Run these 2 commands before running docker-compose up

docker run --rm -v ${PWD}:/app -w /app alpine sh -c "rm -f /app/composer.lock"

docker run --rm -v ${PWD}:/app -w /app composer update --ignore-platform-req=ext-pdo_mysql


