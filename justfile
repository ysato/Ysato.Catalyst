# Common variables

PHP_IMAGE := "php-composer-8.2:local"

@help:
    echo "Usage:"
    echo "  just build        - Build the Docker image."
    echo "  just install      - Run all linting tasks."
    echo "  just test         - Run all linting tasks."
    echo "  just lint         - Run all linting tasks."
    echo "  just clean        - Clean up Docker images."
    echo "  just help         - Show this help message."

build:
    docker build -t {{ PHP_IMAGE }} .

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

clean:
    docker rmi {{ PHP_IMAGE }} || true
