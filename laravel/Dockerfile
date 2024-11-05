# Используем официальный образ PHP с поддержкой FPM (FastCGI Process Manager)
FROM php:8.1-fpm

# Обновление списка пакетов и установка необходимых зависимостей
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        unzip \
        git \
        libpq-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_pgsql soap \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем только composer файлы для кэширования
COPY composer.lock composer.json ./

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем зависимости проекта с помощью Composer
RUN composer install --no-scripts --no-autoloader

# Копируем все остальные файлы проекта
COPY . .

# Завершаем установку пакетов
RUN composer dump-autoload --optimize

# Настраиваем права доступа к папкам storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Открываем порт 8000 для сервера
EXPOSE 8000

# Команда для запуска Laravel на встроенном сервере
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
