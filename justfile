# Common variables

PHP_IMAGE := "php-composer-8.2:local"
ACT_IMAGE := "act:local"

@help:
    echo "Usage:"
    echo "  just build        - Build the Docker image."
    echo "  just install      - Run all linting tasks."
    echo "  just test         - Run all linting tasks."
    echo "  just lint         - Run all linting tasks."
    echo "  just clean        - Clean up Docker images."
    echo "  just help         - Show this help message."

build: build-php build-act

build-php:
    docker build -t {{ PHP_IMAGE }} -f docker/php/Dockerfile .

build-act:
    docker build -t {{ ACT_IMAGE }} -f docker/act/Dockerfile .

install:
    docker run --rm -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer install

test:
    docker run --rm -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer test

coverage:
    docker run --rm -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer coverage

pcov:
    docker run --rm -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer pcov

lint:
    docker run --rm -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer tests

clean: clean-php clean-act

clean-php:
    docker rmi {{ PHP_IMAGE }} || true

clean-act:
    docker rmi {{ ACT_IMAGE }} || true
