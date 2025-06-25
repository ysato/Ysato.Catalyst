# Ysato.Catalyst

Laravelプロジェクトのセットアップを加速させるスキャフォールディングツール。

## このパッケージについて

このパッケージは、拡張性を高めた統一テンプレートシステムを使用して、Laravelプロジェクトの初期設定に必要なファイル群を生成します。

**重要な注意点として、このコマンドは既存のファイルを常に上書きします。**
意図しない変更を防ぐため、コマンド実行後は`git diff`などを通じて差分を注意深く確認し、必要な変更のみをコミットしてください。

ツールの役割は最新のテンプレートに基づいて差分を生成することであり、その差分をどう扱うかは利用者に委ねられます。

## インストール

### 前提条件（強く推奨）

このパッケージでは、Dockerベースの開発ワークフローを簡素化するコマンドランナー [just](https://github.com/casey/just) のインストールを強く推奨します。

お使いのプラットフォーム用の[インストールガイド](https://github.com/casey/just?tab=readme-ov-file#packages)を参照してください。例えば、macOSでHomebrewを使用する場合：

```shell
brew install just
```

### パッケージのインストール

以下のコマンドでインストールします。

```shell
composer require --dev ysato/catalyst
```

## 使用方法

このコマンドは、引数なしで実行すると対話形式で必要な情報を質問します。スクリプトなどで自動化する場合は、引数として情報を渡すことで対話なしで実行できます。

#### 対話形式での実行

引数を何も指定せずにコマンドを実行すると、ベンダー名、パッケージ名、PHPバージョンを順番に質問されます。

```shell
php artisan catalyst:scaffold
```

#### 引数を指定して実行（自動化向け）

CI/CDスクリプトなどで使用する場合、以下の形式で引数を渡すことで、対話なしで実行できます。

**書式:**
```shell
php artisan catalyst:scaffold <vendor> <package> <php>
```

| 引数 | 説明 |
| :--- | :--- |
| **`<vendor>`** | `(必須)` PHPの名前空間に使用するベンダー名 (例: `Ysato`) |
| **`<package>`** | `(必須)` ベンダーに続くパッケージ名 (例: `Catalyst`) |
| **`<php>`** | `(必須)` プロジェクトに設定するPHPバージョン (例: `8.3`) |

**実行例:**
```shell
php artisan catalyst:scaffold MyVendor MyProject 8.3
```

#### オプション

| オプション名 | 説明 |
| :--- | :--- |
| **`--with-ca-file`** | `(任意)` コンテナ内で信頼させるカスタムCA証明書へのパスを指定します。企業のプロキシ下などで必要になります。 |

**引数とオプションを組み合わせた使用例:**
```shell
php artisan catalyst:scaffold MyCorp WebApp 8.3 --with-ca-file=./certs/certificate.pem
```

## セットアップ後の手動ステップ

### 初回QAセットアップ

新しくスキャフォールドされたプロジェクトで初回 `just composer lints` を実行する際、既存のコードパターンが原因でエラーが発生する場合があります。これを解決するため、各QAツール用のベースラインファイルを作成してください：

#### PHP_CodeSnifferベースライン
```shell
vendor/bin/phpcs --report=\\DR\\CodeSnifferBaseline\\Reports\\Baseline --report-file=phpcs.baseline.xml --basepath=.
```

#### PHPMDベースライン
```shell
vendor/bin/phpmd app,src text ./phpmd.xml --generate-baseline
```

#### PHPStanベースライン
```shell
vendor/bin/phpstan analyse --generate-baseline
```

#### Psalmベースライン
```shell
vendor/bin/psalm --set-baseline
```

### Laravel IDE Helperセットアップ

IDE支援とオートコンプリート機能を強化するために、[Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper)コマンドを実行してください：

```shell
php artisan ide-helper:generate
php artisan ide-helper:models -N
php artisan ide-helper:meta
```

### ブランチ保護ルールセットのインポート

このプロジェクトは、`.github/rulesets`ディレクトリに、あらかじめ定義されたGitHubブランチ保護ルールセットをJSONファイルとして生成します。これらは手動でリポジトリに適用する必要があります。

#### 前提条件
* ルールセットをインポートしたいGitHubリポジトリへの**管理者アクセス権**が必要です。

#### GitHub UIを介したインポート手順
1.  GitHub上でリポジトリに移動します。
2.  **Settings** をクリックします。
3.  左サイドバーの "Code and automation" セクションにある **Rules** をクリックし、次に **Rulesets** をクリックします。
4.  **"Import ruleset"** ボタンをクリックします。
5.  JSONファイルのアップロードを求められたら、`.github/rulesets/` 内の `.json` ファイルをアップロードします。
6.  インポートされた設定を確認し、**"Create"** をクリックします。
7.  必要に応じて、他のルールセットファイルについてもこの手順を繰り返します。

    **注意:** インポート後、各ルールセットの "Target branches" セクションを注意深く確認し、意図したブランチ（例: `main`, `develop`）に適用されていることを確かめてください。

## コントリビューター向け

### 開発環境のセットアップ
このプロジェクトは開発にDockerを使用します。用意された`justfile`コマンドを使用してください。

### 利用可能なコマンド
- `just build` - 必要なDockerイメージをビルド
- `just composer` - Docker経由でcomposerコマンドを実行
- `just act` - GitHub Actionsをローカルで実行
- `just clean` - Dockerイメージを削除
- `just help` - このヘルプメッセージを表示

### 互換性テスト
依存関係の最も古い互換バージョンをインストールし、多様な環境での動作を保証するには、以下のコマンドを実行します。
```shell
composer update --prefer-lowest
```

## ライセンス

このパッケージはMITライセンスの下で提供されています。
