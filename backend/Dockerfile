FROM php:8.3-apache

# Extensões necessárias pro Laravel + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite pro Laravel
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copia o código do backend pro container
COPY . .

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala dependências do Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissões pra storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Aponta o DocumentRoot do Apache para /public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
