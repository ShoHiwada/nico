# --- Node.jsとPHP両方使う構成 ---

# Stage 1: Node.js でViteビルド
FROM node:20-bullseye as vite-build

WORKDIR /app
COPY . .

RUN npm ci
RUN npm run build

# Stage 2: PHP環境構築
FROM php:8.2-cli

WORKDIR /app

# 必要なPHP拡張をインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath

# Composerを使えるようにする
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravelコード一式をコピー
COPY . .

# Viteでビルドした成果物をコピー（public/buildなど）
COPY --from=vite-build /app/public/build public/build

# Laravel初期化
RUN composer install --no-interaction --optimize-autoloader
RUN php artisan config:clear

# SQLite DBファイルを作成
RUN touch /tmp/database.sqlite \
    && chmod -R 775 storage bootstrap/cache

# ポートを指定して起動
CMD php artisan serve --host=0.0.0.0 --port=10000
