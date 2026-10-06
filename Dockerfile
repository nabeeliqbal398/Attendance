FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js + Bun for frontend build
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g bun

WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy package files for frontend
COPY package.json bun.lock ./
RUN bun install

# Copy the rest of the app
COPY . .

# Rebuild autoload with all app classes
RUN composer dump-autoload --optimize --no-dev

# Build frontend
RUN node node_modules/vite/bin/vite.js build

# Create storage directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start command (migrations run via Railway's pre-deploy command, never from CMD)
COPY docker/railway-start.sh /usr/local/bin/railway-start
RUN chmod +x /usr/local/bin/railway-start
CMD ["railway-start"]
