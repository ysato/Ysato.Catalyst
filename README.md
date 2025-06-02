このコマンドは、依存ライブラリの最も古い互換バージョンをインストールし、  
本パッケージが多様な環境で安定して動作することを確認するために使用します。

```shell
composer update --prefer-lowest
```

対応予定
- phpstan（larastan）
- psalm
  - それぞれにプラグインがあることも忘れないこと
- phpunit
  - 既にLaravelにテストコマンドがあるのでどうするか検討中