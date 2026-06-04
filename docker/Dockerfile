FROM php:8.4.21-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    gnupg2 \
    apt-transport-https \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    unixodbc-dev \
    zip \
    unzip \
    nodejs \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install mbstring exif pcntl bcmath gd \
    && corepack enable \
    && corepack prepare pnpm@10 --activate \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/framework/testing bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && pnpm install --frozen-lockfile \
    && pnpm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
