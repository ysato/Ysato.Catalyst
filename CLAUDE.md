# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ysato.Catalyst is a Laravel package that provides scaffolding for new PHP projects. It generates project structure, Docker configuration, composer manifests, and development tools using a step-based architecture.

The package implements a command pattern where each scaffolding operation is a "Step" that implements `StepInterface`. The main command `catalyst:scaffold` orchestrates these steps to generate a complete project structure from template stubs.

## Key Commands

### Testing
- `just test` - Run PHPUnit tests without coverage
- `just coverage` - Generate test coverage with Xdebug
- `just pcov` - Generate test coverage with PCOV

### Docker Development (via justfile)
- `just build` - Build Docker images for PHP and Act
- `just install` - Install dependencies via Docker
- `just test` - Run tests via Docker
- `just lint` - Run linting and quality checks
- `just act` - Run GitHub Actions locally
- `just clean` - Remove Docker images

### Package Usage
- `php artisan catalyst:scaffold` - Interactive scaffolding
- `php artisan catalyst:scaffold Vendor Package 8.3` - Non-interactive scaffolding
- `php artisan catalyst:scaffold Vendor Package 8.3 --with-ca-file=./certs/certificate.pem` - With custom CA certificate

## Architecture

### Step-Based Processing
All scaffolding operations implement `Ysato\Catalyst\Steps\StepInterface` and are executed sequentially:

1. `PrepareSandboxFromStubs` - Copies template files to temporary directory
2. `GenerateGitignore` - Creates .gitignore file
3. `ScaffoldComposerManifest` - Generates composer.json
4. `ScaffoldDocker` - Creates Docker configuration
5. `ReplacePlaceholders` - Substitutes template variables

### Template System
- Stub files in `src/stubs/` serve as templates
- Placeholders like `__Package__`, `__Vendor__` are replaced during scaffolding
- Docker files are conditionally generated based on CA certificate requirements

### Testing Strategy
- Unit tests in `tests/` directory
- Integration tests verify complete scaffolding workflow
- Expected output files in `tests/expected/` for comparison
- CI tests against multiple PHP versions (8.2, 8.3, 8.4) and Laravel versions (11.x, 12.x)

## Development Notes

### Docker Setup
The project uses custom Docker images built from `docker/composer/Dockerfile` and `docker/act/Dockerfile`. These support corporate proxy scenarios with custom CA certificates.

### Laravel Integration
The package registers as a Laravel service provider (`CatalystServiceProvider`) and provides the `catalyst:scaffold` Artisan command.

### Supported PHP Versions
Currently supports PHP 8.2, 8.3, and 8.4. Version validation is enforced in the scaffolding command.
