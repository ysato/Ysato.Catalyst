PHP_IMAGE := "php-composer-8.2:local"
ACT_IMAGE := "act:local"

@help:
    echo "Usage:"
    echo "  just build        - Builds the necessary Docker images."
    echo "  just install      - Installs project dependencies."
    echo "  just test         - Runs the test suite."
    echo "  just coverage     - Generates a code coverage report."
    echo "  just pcov         - Generates a coverage report using PCOV."
    echo "  just lint         - Runs all linting tasks."
    echo "  just act          - Runs GitHub Actions locally."
    echo "  just clean        - Removes the Docker images."
    echo "  just help         - Displays this help message."

build: build-php build-act

build-php:
    docker build -t {{ PHP_IMAGE }} -f docker/composer/Dockerfile .

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

act *options:
    act {{ options }}

clean: clean-php clean-act

clean-php:
    docker rmi {{ PHP_IMAGE }} || true

clean-act:
    docker rmi {{ ACT_IMAGE }} || true
