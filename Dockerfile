FROM php:8.2-cli

# 必要なPHP拡張とツールをインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath

# Composer インストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリを作成
WORKDIR /app

# プロジェクトをコンテナにコピー
COPY . .

# DBファイルの作成と権限設定
RUN touch /tmp/database.sqlite \
 && chmod -R 775 storage bootstrap/cache

# Laravel 初期化
RUN composer install --no-interaction --optimize-autoloader \
 && php artisan config:clear \
 && php artisan migrate --force

# アプリ起動
CMD php artisan serve --host=0.0.0.0 --port=10000
