# Ysato.Catalyst

A scaffolding tool to accelerate the setup of Laravel projects.

## About This Package

This package generates the necessary file structure for initializing a project.

**Important Note: This command will always overwrite existing files.**
To prevent unintended changes, you are expected to carefully review the output with a tool like `git diff` after running the command, and then selectively commit only the changes you want to adopt.

The tool's role is to generate a diff based on the latest template; how that diff is handled is delegated to the user.

## Installation

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

### Importing Branch Protection Rulesets

This project generates predefined GitHub branch protection rulesets as JSON files in the `.github/rulesets` directory. These must be manually applied to your repository.

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

## For Contributors

To install the oldest compatible versions of dependencies and ensure this package works reliably in diverse environments, run the following command:
```shell
composer update --prefer-lowest
```

## License

This package is provided under the MIT License.
