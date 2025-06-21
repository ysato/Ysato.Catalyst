COMPOSER_IMAGE := "php-composer-8.4:local"
ACT_IMAGE := "act:local"
SPECTRAL_IMAGE := "stoplight/spectral:latest"

@help:
    echo "Usage:"
    echo "  just build        - Builds the necessary Docker images."
    echo "  just composer     - Runs composer commands via Docker."
    echo "  just spectral     - Runs OpenAPI linting with Spectral."
    echo "  just act          - Runs GitHub Actions locally."
    echo "  just clean        - Removes the Docker images."
    echo "  just help         - Displays this help message."

build: build-composer build-act

build-composer:
    docker build -t {{ COMPOSER_IMAGE }} -f docker/composer/Dockerfile .

build-act:
    docker build -t {{ ACT_IMAGE }} -f docker/act/Dockerfile .

build-spectral:
    docker pull {{ SPECTRAL_IMAGE }}

composer *arguments:
    docker run --rm -it -v "$(pwd):/var/www/html" {{ COMPOSER_IMAGE }} composer {{ arguments }}

spectral:
    @if {{ path_exists("openapi.yaml") }}; then \
        docker run --rm -it -v $(pwd):/tmp -w /tmp \
        {{ SPECTRAL_IMAGE }} lint openapi.yaml; \
    else \
        echo "\033[33mwarn\033[0m: No openapi.yaml found, skipping OpenAPI linting."; \
    fi

act *options:
    act {{ options }}

clean: clean-composer clean-act clean-spectral

clean-composer:
    docker rmi {{ COMPOSER_IMAGE  }} || true

clean-act:
    docker rmi {{ ACT_IMAGE }} || true

clean-spectral:
    docker rmi {{ SPECTRAL_IMAGE }} || true
