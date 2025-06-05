# Ysato.Catalyst

A command package to streamline the setup of Laravel projects.  
This package provides commands to easily configure project architecture, metadata, and development standards.

## Installation

Run the following command to install:

```shell
composer require --dev ysato/catalyst
```

## Usage

After installation, you can start the setup using the following command:

```shell
php artisan catalyst:setup
```

## Command List

| Command Name                | Description                                            |
|-----------------------------|--------------------------------------------------------|
| `catalyst:setup`            | Executes the overall project setup.                    |
| `catalyst:metadata`         | Generates composer.json metadata.                      |
| `catalyst:architecture-src` | Initializes recommended src architecture.              |
| `catalyst:phpcs`            | Initializes PHP Code Sniffer configuration.            |
| `catalyst:phpmd`            | Initializes PHP Mess Detector configuration.           |
| `catalyst:spectral`         | Initializes Spectral (OpenAPI linter) configuration.   |
| `catalyst:github`           | Sets up recommended GitHub workflows and rulesets.     |
| `catalyst:ide`              | Initializes recommended IDE (e.g., PhpStorm) settings. |
| `catalyst:act`              | Configures local execution for GitHub Actions.         |

## For Contributors

The following command installs the oldest compatible versions of dependencies  
to ensure this package works reliably in diverse environments:

```shell
composer update --prefer-lowest
```

## Contribution Guidelines

1. Fork this repository.
2. Create a new branch.
3. Make the necessary changes and commit them.
4. Submit a pull request.

## License

This package is provided under the MIT License.
