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

## Importing Branch Protection Rulesets

This project contains predefined GitHub branch protection rulesets stored as JSON files. You can import these into your GitHub repository to quickly apply consistent branch protection rules.

The following ruleset files are available in the `.github/rulesets` directory:

* `branch-all-users-rules.json`: Contains rules generally applicable to all users.
* `branch-exclude-core-contributors-rule.json`: Contains rules that might exclude core contributors from certain restrictions, or apply specific rules to them.

### Prerequisites

* You need **admin access** to the GitHub repository where you want to import these rulesets.

### Importing via GitHub UI

GitHub allows you to import ruleset configurations directly.

1.  Navigate to your repository on GitHub.
2.  Click on **Settings**.
3.  In the left sidebar, under the "Code and automation" section, click on **Rules**, then **Rulesets**.
4.  Click the **"Import ruleset"** button (this option might be under a "..." menu or directly visible depending on UI updates).
5.  You will be prompted to upload a JSON file.
6.  Upload ` .github/rulesets/branch-all-users-rules.json`.
7.  Review the imported settings and click **"Create"**.
8.  Repeat steps 4-7 for `.github/rulesets/branch-exclude-core-contributors-rule.json`.

    **Note:** Carefully review the "Target branches" section for each ruleset after import to ensure they apply to the intended branches (e.g., `main`, `develop`, `feature/*`). You might need to adjust these based on your repository's branching strategy.

### Verifying the Import

After importing, go to **Settings > Rules > Rulesets** in your GitHub repository to verify that the new rulesets appear and are configured as expected. Check their enforcement status and target branches.

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
