# PHP 8.2の公式fpm-alpineイメージをベースにする
FROM php:8.2-fpm-alpine

# Composerのインストール
# マルチステージビルドでcomposer:latestイメージから/usr/bin/composerをコピー
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを設定
WORKDIR /var/www/html
