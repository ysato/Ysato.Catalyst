# Ysato.Catalyst

Laravelプロジェクトのセットアップを効率化するためのコマンドパッケージです。  
このパッケージは、プロジェクトのアーキテクチャ、メタデータ、開発標準を簡単に設定するためのコマンドを提供します。

## インストール

以下のコマンドを実行してインストールしてください。

```shell
composer require --dev ysato/catalyst
```

## 使用方法

インストール後、以下のコマンドを使用してセットアップを開始できます。

```shell
php artisan catalyst:setup
```

## コマンド一覧

以下は、READMEで使用するためのコマンド名と説明の日本語訳です。

---

## コマンド一覧

| コマンド名                       | 説明                                        |
|:----------------------------|:------------------------------------------|
| `catalyst:setup`            | プロジェクト全体のセットアップを実行します。                    |
| `catalyst:metadata`         | `composer.json` のメタデータを生成します。             |
| `catalyst:architecture-src` | 推奨される `src` ディレクトリの構成を初期化します。             |
| `catalyst:phpcs`            | PHP Code Sniffer の設定を初期化します。              |
| `catalyst:phpmd`            | PHP Mess Detector の設定を初期化します。             |
| `catalyst:spectral`         | Spectral (OpenAPI リンター) の設定を初期化します。       |
| `catalyst:github`           | 推奨される GitHub Actions ワークフローとルールセットを設定します。 |
| `catalyst:ide`              | 推奨される IDE (例: PhpStorm) の設定を初期化します。       |
| `catalyst:act`              | GitHub Actions のローカル実行を設定します。             |

## 貢献者向け

以下のコマンドは、依存ライブラリの最も古い互換バージョンをインストールし、  
本パッケージが多様な環境で安定して動作することを確認するために使用します。

```shell
composer update --prefer-lowest
```

## 貢献方法

1. このリポジトリをフォークします。
2. 新しいブランチを作成します。
3. 必要な変更を加え、コミットします。
4. プルリクエストを送信してください。

## ライセンス

このパッケージは、MITライセンスの下で提供されています。
