PHP_IMAGE := "php-composer-8.2:local"
ACT_IMAGE := "act:local"

@help:
    echo "Usage:"
    echo "  just build        - Builds the necessary Docker images."
    echo "  just composer     - Runs composer commands via Docker."
    echo "  just act          - Runs GitHub Actions locally."
    echo "  just clean        - Removes the Docker images."
    echo "  just help         - Displays this help message."

build: build-php build-act

build-php:
    docker build -t {{ PHP_IMAGE }} -f docker/composer/Dockerfile .

build-act:
    docker build -t {{ ACT_IMAGE }} -f docker/act/Dockerfile .

composer *arguments:
    docker run --rm -it -v "$(pwd):/var/www/html" {{ PHP_IMAGE }} composer {{ arguments }}

act *options:
    act {{ options }}

clean: clean-php clean-act

clean-php:
    docker rmi {{ PHP_IMAGE }} || true

clean-act:
    docker rmi {{ ACT_IMAGE }} || true
