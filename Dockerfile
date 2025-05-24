FROM php:8.2-cli

# 必要なPHP拡張とツールをインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_sqlite

# Composer インストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /app

# プロジェクトコピー
COPY . .

# 依存関係インストール
RUN composer install --no-interaction --optimize-autoloader

# ポート指定して Laravel 起動
CMD php artisan serve --host=0.0.0.0 --port=10000
