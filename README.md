# Ysato.Catalyst

A scaffolding tool to accelerate the setup of Laravel projects.

## About This Package

This package generates the necessary file structure for initializing a Laravel project using a unified template system with enhanced extensibility.

**Important Note: This command will always overwrite existing files.**
To prevent unintended changes, you are expected to carefully review the output with a tool like `git diff` after running the command, and then selectively commit only the changes you want to adopt.

The tool's role is to generate a diff based on the latest template; how that diff is handled is delegated to the user.

## Installation

### Prerequisites (Strongly Recommended)

This package strongly recommends installing [just](https://github.com/casey/just), a command runner that simplifies Docker-based development workflows.

Please refer to the [installation guide](https://github.com/casey/just?tab=readme-ov-file#packages) for your platform. For example, on macOS with Homebrew:

```shell
brew install just
```

### Package Installation

Run the following command to install:

```shell
composer require --dev ysato/catalyst
```

## Usage

This command will prompt you for necessary information interactively when run without arguments. For automation, you can pass the information as arguments to run it non-interactively.

#### Interactive Execution

If you run the command without any arguments, you will be prompted sequentially for the vendor name, package name, and PHP version.

```shell
php artisan catalyst:scaffold
```

#### Execution with Arguments (for Automation)

For use in CI/CD scripts, you can run the command non-interactively by passing arguments in the following format.

**Format:**
```shell
php artisan catalyst:scaffold <vendor> <package> <php>
```

| Argument | Description |
| :--- | :--- |
| **`<vendor>`** | `(Required)` The vendor name for namespacing (e.g., `Ysato`). |
| **`<package>`** | `(Required)` The package name that follows the vendor (e.g., `Catalyst`). |
| **`<php>`** | `(Required)` The PHP version to configure for the project (e.g., `8.3`). |

**Example:**
```shell
php artisan catalyst:scaffold MyVendor MyProject 8.3
```

#### Options

| Option | Description |
| :--- | :--- |
| **`--with-ca-file`** | `(Optional)` Path to a custom CA certificate file to trust within the container. Needed when behind a corporate proxy. |

**Example with Arguments and Options:**
```shell
php artisan catalyst:scaffold MyCorp WebApp 8.3 --with-ca-file=./certs/certificate.pem
```

## Post-Setup Manual Steps

### Initial QA Setup

When running `just composer lints` for the first time on a newly scaffolded project, you may encounter errors due to existing code patterns. To resolve these, create baseline files for each QA tool:

#### PHP_CodeSniffer Baseline
```shell
vendor/bin/phpcs --report=\\DR\\CodeSnifferBaseline\\Reports\\Baseline --report-file=phpcs.baseline.xml --basepath=.
```

#### PHPMD Baseline
```shell
vendor/bin/phpmd app,src text ./phpmd.xml --generate-baseline
```

#### PHPStan Baseline
```shell
vendor/bin/phpstan --generate-baseline
```

Add the generated baseline file to the includes in phpstan.neon:
```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/nesbot/carbon/extension.neon
+    - phpstan-baseline.neon

...
```

#### Psalm Baseline
```shell
vendor/bin/psalm --set-baseline
```

### Laravel IDE Helper Setup

For enhanced IDE support and autocompletion, run the [Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper) commands:

```shell
php artisan ide-helper:generate
php artisan ide-helper:models -N
php artisan ide-helper:meta
```

### OpenAPI Validation in Feature Tests

This package provides two types of OpenAPI validation capabilities for Feature tests:

Place your OpenAPI specification at the project root as `openapi.yaml`, or configure the path via `OPENAPI_SPEC_PATH` environment variable.

#### 1. Request/Response Validation (`ValidatesOpenApiSpec`)

Automatically validates that requests called from test cases and returned responses comply with the OpenAPI specification.

#### 2. Specification Coverage Verification (`Spectatable`)

Verifies whether the specifications described in the OpenAPI specification are being called from tests.

```shell
composer spectate
```

This displays a report showing which endpoints and status codes have been tested:

```
+-------------+--------+-------------------------------------------+-------------+
| IMPLEMENTED | METHOD | ENDPOINT                                  | STATUS CODE |
+-------------+--------+-------------------------------------------+-------------+
| ✅          | GET    | /threads                                  | 200         |
| ✅          | POST   | /threads                                  | 201         |
| ✅          | POST   | /threads                                  | 422         |
| ✅          | POST   | /threads                                  | 401         |
| ✅          | GET    | /threads/{threadid}                       | 200         |
| ✅          | GET    | /threads/{threadid}                       | 404         |
| ❌          | PUT    | /threads/{threadid}                       | 204         |
| ❌          | PUT    | /threads/{threadid}                       | 401         |
| ❌          | PUT    | /threads/{threadid}                       | 403         |
| ❌          | PUT    | /threads/{threadid}                       | 404         |
| ❌          | PUT    | /threads/{threadid}                       | 422         |
| ❌          | DELETE | /threads/{threadid}                       | 204         |
| ❌          | DELETE | /threads/{threadid}                       | 401         |
| ❌          | DELETE | /threads/{threadid}                       | 403         |
| ❌          | DELETE | /threads/{threadid}                       | 404         |
| ❌          | POST   | /threads/{threadid}/scratches             | 201         |
| ❌          | POST   | /threads/{threadid}/scratches             | 401         |
| ❌          | POST   | /threads/{threadid}/scratches             | 403         |
| ❌          | POST   | /threads/{threadid}/scratches             | 404         |
| ❌          | POST   | /threads/{threadid}/scratches             | 422         |
| ❌          | PUT    | /threads/{threadid}/scratches/{scratchid} | 204         |
| ❌          | PUT    | /threads/{threadid}/scratches/{scratchid} | 401         |
| ❌          | PUT    | /threads/{threadid}/scratches/{scratchid} | 403         |
| ❌          | PUT    | /threads/{threadid}/scratches/{scratchid} | 404         |
| ❌          | PUT    | /threads/{threadid}/scratches/{scratchid} | 422         |
| ❌          | DELETE | /threads/{threadid}/scratches/{scratchid} | 204         |
| ❌          | DELETE | /threads/{threadid}/scratches/{scratchid} | 401         |
| ❌          | DELETE | /threads/{threadid}/scratches/{scratchid} | 403         |
| ❌          | DELETE | /threads/{threadid}/scratches/{scratchid} | 404         |
+-------------+--------+-------------------------------------------+-------------+
```

### Importing GitHub Rulesets

This project generates predefined GitHub rulesets as JSON files in the `.github/rulesets` directory. These must be manually applied to your repository.

#### Prerequisites
* You need **admin access** to the GitHub repository where you want to import these rulesets.

#### Importing via GitHub UI
1.  Navigate to your repository on GitHub.
2.  Click on **Settings**.
3.  In the left sidebar, under the "Code and automation" section, click on **Rules**, then **Rulesets**.
4.  Click the **"Import ruleset"** button.
5.  When prompted to upload a JSON file, select a `.json` file from the `.github/rulesets/` directory.
6.  Review the imported settings and click **"Create"**.
7.  Repeat for other ruleset files as needed.

    **Note:** Carefully review the "Target branches" section for each ruleset after import to ensure they apply to the intended branches (e.g., `main`, `develop`).

## Documentation

Detailed documentation is available on the [GitHub wiki](https://github.com/ysato/Ysato.Catalyst/wiki).

## For Contributors

### Development Setup
This project uses Docker for development. Use the provided `justfile` commands.

### Available Commands
- `just build` - Builds the necessary Docker images
- `just composer` - Runs composer commands via Docker
- `just act` - Runs GitHub Actions locally
- `just clean` - Removes the Docker images
- `just help` - Displays this help message

### Compatibility Testing
To install the oldest compatible versions of dependencies and ensure this package works reliably in diverse environments, run the following command:
```shell
composer update --prefer-lowest
```

## License

This package is provided under the MIT License.
