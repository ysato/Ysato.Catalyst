# CLAUDE.md

このファイルは Claude Code (claude.ai/code) がこのリポジトリでコード作業を行う際のガイダンスを提供します。

## プロジェクト概要

Ysato.Catalyst は新しい PHP プロジェクトのためのスキャフォールディングを提供する Laravel パッケージです。統一されたテンプレートシステムを使用して、プロジェクト構造、Docker 設定、composer マニフェスト、開発ツールを生成します。

パッケージはスキャフォールディングエンジンアーキテクチャを実装しており、メインコマンド `catalyst:scaffold` がテンプレートスタブから完全なプロジェクト構造を生成します。

### トランザクション的処理の特性

**一時ディレクトリを使用する理由:**
このパッケージは「All or Nothing」のトランザクション的処理を保証するため、必ず一時ディレクトリを経由します。

**処理の流れ:**
1. **一時ディレクトリでの全処理**: すべてのファイル生成・レンダリングを一時ディレクトリ内で実行
2. **アトミックなコピー**: 全処理が成功した場合のみプロジェクトディレクトリに一括コピー
3. **失敗時の安全性**: エラー発生時は一時ディレクトリを削除し、プロジェクトディレクトリは元の状態を保持

**保証される特性:**
- **原子性**: プロジェクトディレクトリの変更が一瞬で完了
- **一貫性**: 中途半端な状態のプロジェクトが生成されない
- **安全性**: 失敗時に手動クリーンアップが不要

この設計により、スキャフォールディング処理中にエラーが発生しても、プロジェクトディレクトリが破損状態で残ることを防ぎます。

### スキャフォールディング処理の特性

**テンプレート処理の複雑性:**
このパッケージは単純な文字列置換を超えた高度なテンプレート処理を行います。

**主要な特性:**
1. **条件分岐レンダリング**: 単なる文字列置換ではなく、if文やループなどの制御構造を含むテンプレート処理
   - 例: `{% if has_ca %}` による CA 証明書設定の有無による出し分け
   
2. **動的ファイル名レンダリング**: ファイル名自体も変数置換の対象
   - 例: `__Package__.php` → `MyPackage.php`
   - プレースホルダーからTwig変数への変換も含む
   
3. **動的ファイル生成**: stubsディレクトリには存在しない、実行時に動的に作られるファイル
   - 例: composer.json（既存のcomposer.jsonをベースに動的生成）
   - 空ファイルをstubsに配置する選択肢もあるが、動的生成の方が柔軟性が高い

**これらの特性の影響:**
- 単純な文字列処理ライブラリでは対応困難
- Twigなどのテンプレートエンジンが必要
- ファイル名とコンテンツの両方でレンダリング処理が必要
- 静的テンプレートと動的生成の両方を統一的に扱う仕組みが必要

## 主要コマンド

### テスト
- `just test` - カバレッジなしで PHPUnit テストを実行
- `just coverage` - Xdebug でテストカバレッジを生成
- `just pcov` - PCOV でテストカバレッジを生成
- `just tests` - lint、QA、テストを実行する統合コマンド

### Docker 開発 (justfile 経由)
- `just build` - PHP と Act の Docker イメージをビルド
- `just install` - Docker 経由で依存関係をインストール
- `just test` - Docker 経由でテストを実行
- `just tests` - Docker経由でlint、QA、テストを実行
- `just act` - GitHub Actions をローカルで実行
- `just clean` - Docker イメージを削除

### パッケージ使用方法
- `php artisan catalyst:scaffold` - インタラクティブスキャフォールディング
- `php artisan catalyst:scaffold Vendor Package 8.3` - 非インタラクティブスキャフォールディング
- `php artisan catalyst:scaffold Vendor Package 8.3 --with-ca-file=./certs/certificate.pem` - カスタム CA 証明書付き

## アーキテクチャ

### 統一されたスキャフォールディングシステム
シンプルで理解しやすい3段階のスキャフォールディング処理を実装：

**コアコンポーネント:**
- `Scaffolder` - メインのスキャフォールディング実行クラス（ファクトリメソッド付き）
- `Sandbox` - トランザクション的処理のための一時ディレクトリ管理
- `Context` - スキャフォールディングに必要な変数（vendor、package、php等）を保持
- `Input` - ユーザー入力を表現するデータクラス
- `Template\Renderer` - プレースホルダー変換とTwigレンダリングを統合
- `Template\CaseFilters` - Twigテンプレート用のケース変換フィルター（pascal、kebab、snake）

**処理フロー:**
1. **ファイルコピー**: stubsディレクトリから一時ディレクトリにファイル名をレンダリングしてコピー
2. **composer.json生成**: 既存のcomposer.jsonをベースに動的生成
3. **変数レンダリング**: 全ファイル内のTwig変数をコンテキスト値でレンダリング
4. **コミット**: 全処理成功時に一時ディレクトリからプロジェクトディレクトリに一括コピー

### テンプレートシステム
**テンプレート配置:**
- `/stubs/` ディレクトリ内にTwig構文テンプレートファイルを配置
- Laravel慣習に従い、プロジェクトルートの `/stubs/` を使用

**動的ファイル命名:**
- プレースホルダーによるファイル名変換: `__Package__.php` → `{{ package|pascal }}.php`
- プレースホルダーマップ: `__Package__` → `{{ package|pascal }}`, `__Vendor__` → `{{ vendor|pascal }}`

**Twigテンプレート機能:**
- ケース変換フィルター: `{{ vendor|pascal }}`, `{{ package|kebab }}`, `{{ package|snake }}`
- 条件分岐レンダリング: `{% if has_ca %}` でCA証明書設定の有無による出し分け
- Context変数アクセス: `vendor`, `package`, `php`, `with_ca`, `gitignore_content`, `has_ca`

**Sandboxパターン:**
トランザクション的処理を保証するSandboxパターンを実装：
- `SandboxInterface` - サンドボックス操作の抽象化
- `create()` - 一時ディレクトリ作成
- `execute()` - サンドボックス内でのコールバック実行
- `commit()` - サンドボックスからプロジェクトディレクトリへの一括コピー
- `delete()` - 一時ディレクトリのクリーンアップ

**Context生成:**
`Context::fromInputAndGitignorePath()` でInputから生成：
- `.gitignore` ファイルからの不要パターン除去（`.idea`, `.php_cs.cache`, `.phpcs-cache`）
- CA証明書ファイルパスの検証
- 元のgitignore内容の保持

**ケース変換フィルター詳細:**
`Template\CaseFilters` クラスがIlluminate\Support\Strを使用して以下のTwigフィルターを提供：
- `pascal` - PascalCase変換（例: `my-package` → `MyPackage`）
- `kebab` - kebab-case変換（例: `MyPackage` → `my-package`）
- `snake` - snake_case変換（例: `MyPackage` → `my_package`）

使用例:
```twig
namespace {{ vendor|pascal }}\{{ package|pascal }};
"name": "{{ vendor|kebab }}/{{ package|kebab }}",
protected string ${{ package|snake }}_config;
```

### テスト戦略
**テスト構成:**
- `tests/` ディレクトリの単体テスト・統合テスト
- 依存性注入テストによる各コンポーネントの独立したテスト
- 完全なスキャフォールディングワークフローを検証する統合テスト

**スナップショットテスト:**
- spatie/phpunit-snapshot-assertions を使用
- カスタム `FileTreeDriver` でディレクトリ構造とファイル内容を比較
- 生成されたプロジェクト構造の完全性を検証
- データプロバイダーで複数のコンテキスト組み合わせをテスト

**CI/CD テスト:**
- 複数の PHP バージョン（8.2, 8.3, 8.4）での動作確認
- 複数の Laravel バージョン（11.x, 12.x）での互換性テスト
- GitHub Actions による自動テスト実行

## 開発ノート

### Docker セットアップ
プロジェクトは `docker/composer/Dockerfile` と `docker/act/Dockerfile` からビルドされたカスタム Docker イメージを使用します。これらは企業プロキシシナリオとカスタム CA 証明書をサポートします。

### Laravel 統合
パッケージは Laravel サービスプロバイダー（`CatalystServiceProvider`）として登録され、`catalyst:scaffold` Artisan コマンドを提供します。

### サポート PHP バージョン
現在 PHP 8.2、8.3、8.4 をサポートしています。バージョン検証はスキャフォールディングコマンドで実行されます。

# Code Quality Principles

すべてのコード変更において、以下の原則を常に考慮すること：

## 保守性 (Maintainability)
- コードの意図が明確で理解しやすい
- 責任が適切に分離されている
- 命名が一貫性があり意味が明確
- 重複が最小化されている

## 拡張性 (Extensibility)  
- 新機能追加時に既存コードの変更が最小限
- インターフェースや抽象化を適切に活用
- 設定や振る舞いがカスタマイズ可能
- オープン/クローズド原則に従っている

## テスト容易性 (Testability)
- 依存関係が注入可能
- 副作用が最小化されている
- 単一責任の原則に従っている
- モック・スタブが作成しやすい

## 設計原則への意識

以下の設計原則を常に意識すること（必ずしも厳密に守る必要はないが、トレードオフを理解した上で判断する）：

- **KISS (Keep It Simple, Stupid)**: シンプルで理解しやすい解決策を選ぶ
- **DRY (Don't Repeat Yourself)**: 重複を避け、知識を一箇所に集約する
- **YAGNI (You Aren't Gonna Need It)**: 現在必要でない機能は実装しない
- **SOLID原則**: 単一責任、開放閉鎖、リスコフ置換、インターフェース分離、依存性逆転

## コーディングスタイル

### フォーマットルール
- **return文の前には必ず1行空ける** - ただし、メソッドがreturn文1行のみの場合はこの限りではない

```php
// ✅ Good: return文の前に空行
public function process(): string
{
    $result = $this->doSomething();
    $processed = $this->transform($result);
    
    return $processed;
}

// ✅ Good: 1行のみのreturnは空行不要
public function getId(): string
{
    return $this->id;
}

// ❌ Bad: return文の前に空行がない
public function calculate(): int
{
    $value = $this->getValue();
    return $value * 2;
}
```

### 命名規則

クラス、メソッド、変数を命名する際は、明確性と具体性を優先する：

- **過度に汎用的な用語を避ける** - `Manager`, `Processor`, `Handler`, `Service`, `Utility` 等は、それが具体的な責任を正確に表現する場合のみ使用
- **ドメイン固有の用語を使用** - 実際のビジネスロジックや技術的文脈を反映した用語を選択
- **具体的なアクションと責任を表現** - 抽象的な概念よりも具体的な機能を示す
- **長い名前を恐れない** - 意図を明確に伝えられるなら長い名前でも良い
- **適切な場合は動作動詞を含める** - `Calculator`, `Validator`, `Builder`, `Parser` など

**例:**
- ✅ `SessionManager` - セッション管理、責任が明確
- ✅ `RuntimeStubGenerator` - 実行時にスタブを生成
- ❌ `DataProcessor` - 汎用的すぎ、どんな処理か不明
- ❌ `FileHandler` - 曖昧、ファイルに何をするのか不明

クラス名がドキュメントとして機能することを目指す - 開発者が名前だけで目的を理解できるように。

## 開発ワークフロー

### コード変更完了時の必須手順
**重要:** 任意のコード変更を完了した際は、必ず以下を実行してください：

1. `just tests` - lint、QA、テストを実行する統合コマンド
2. エラーが発生した場合は修正してから完了とする
3. **スナップショットテストが失敗した場合は絶対に勝手に更新しない** - ユーザーに確認を求める

### スナップショットテストについて
**重要な注意事項:**
- スナップショットテストは生成されるプロジェクト構造の完全性を検証する
- 失敗した場合は、意図しない変更が発生している可能性がある
- **絶対に勝手にスナップショットを更新してはいけない** - 必ずユーザーに確認を求める
- スナップショット更新は慎重に検討し、変更内容を十分に理解してから実行する

### スナップショットテスト更新方法（ユーザー確認後のみ）
```bash
# スナップショット更新
docker run --rm -v "$(pwd):/var/www/html" -e UPDATE_SNAPSHOTS=true php-composer-8.2:local ./vendor/bin/phpunit
```

## 開発プロセス重要事項

**実装前の必須確認:**
- **絶対に実装まで進めずに、まずは計画を立てる**
- **計画段階で必ずユーザーに確認を取る**
- 問題分析、解決方針、具体的な実装手順を明確にしてからユーザー承認を得る
- ユーザーの明示的な承認なしに実装作業（ファイル作成・編集）を開始しない

**README同期ルール:**
- **片方のREADME（README.md または README-ja.md）を更新したら、必ずもう一方も対応する更新を行う**
- 内容の一貫性を保つため、両方のREADMEは常に同期させる
- 一方のみの更新は禁止

## アーキテクチャ決定記録（ADR）

### 作成ルール
- ファイル名: `001_タイトル.md` 形式で連番プレフィックスを付与
- 配置場所: `./adr/` ディレクトリ
- ファイル名は adr ディレクトリ内でユニークとなるようにする

### テンプレート構成
各ADRは以下の構成要素を含む：

1. **概要** - 決定事項の要約
2. **課題** - なぜその問題に取り組むのかを明確に説明
3. **決定事項** - アーキテクチャの方向性を明確に示す
4. **ステータス** - Proposed/Accepted/Rejected/Deprecated/Superseded
5. **詳細**
   - 前提 - 前提条件を明確に記述
   - 制約 - 環境上の制約や追加の制約を記載
   - 検討した選択肢 - 検討した選択肢を具体的に列挙

### 記述ガイドライン
- 簡潔で明確な記述を心がける
- 他者の意見も考慮した内容とする
- 信頼性の高い意思決定記録を目指す
- 最小限の問題のみ文書化する

