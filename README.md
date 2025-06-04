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

During setup, you can choose the following options:
- Configure metadata for composer.json
- Set up the architecture for the src directory
- Configure coding standards and QA tools

## Command List

| Command Name                   | Description                                                          |
|--------------------------------|----------------------------------------------------------------------|
| `catalyst:setup`               | Executes the overall project setup.                                  |
| `catalyst:metadata`            | Configures metadata for composer.json.                               |
| `catalyst:architecture-src`    | Sets up the basic architecture for the src directory.                |
| `catalyst:standards`           | Configures coding standards and QA tools.                            |

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
