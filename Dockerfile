FROM php:8.2-cli

# PHP拡張・ツールインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /app

# プロジェクトコピー
COPY . .

# SQLite DBファイル作成 & パーミッション修正
RUN touch /tmp/database.sqlite \
 && chmod -R 775 storage bootstrap/cache

# Laravel依存インストール
RUN composer install --no-interaction --optimize-autoloader

# アプリ起動時にマイグレーション＋サーバ起動（←これが超重要）
CMD php artisan config:clear && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=10000
