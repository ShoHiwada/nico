# --- Stage 1: Node.jsでViteビルド ---
FROM node:20-bullseye as vite-build

WORKDIR /app
COPY . .

RUN npm ci
RUN npm run build

# --- Stage 2: PHP環境 + Laravel構築 ---
FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

COPY --from=vite-build /app/public/build public/build

# ✅ SQLite DBファイルを作成（Render用：/data に変更）
RUN mkdir -p /data \
    && touch /data/database.sqlite \
    && chmod -R 775 /data \
    && chmod -R 775 storage bootstrap/cache

RUN composer install --no-interaction --optimize-autoloader
RUN php artisan config:clear

# ✅ CMDにマイグレーション追加（起動時に毎回）
# CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
CMD php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=10000

