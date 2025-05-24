# --- Stage 1: Node.jsでViteビルド ---
FROM node:20-bullseye as vite-build

WORKDIR /app
COPY . .

RUN npm ci
RUN npm run build

# --- Stage 2: PHP環境 + Laravel構築 ---
FROM php:8.2-cli

WORKDIR /app

# PHP拡張インストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath

# Composerのセットアップ
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# プロジェクトファイルコピー
COPY . .

# Viteビルド成果物をコピー
COPY --from=vite-build /app/public/build public/build

# SQLite DBファイルを作成（Render用）
RUN touch /tmp/database.sqlite \
    && chmod -R 775 /tmp/database.sqlite \
    && chmod -R 775 storage bootstrap/cache

# Laravel初期化
RUN composer install --no-interaction --optimize-autoloader
RUN php artisan config:clear

# ポート指定してLaravelサーバ起動
CMD php artisan serve --host=0.0.0.0 --port=10000
