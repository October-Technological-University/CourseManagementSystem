# syntax=docker/dockerfile:1

FROM composer:lts as deps

WORKDIR /app
COPY composer.json .
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

################################################################################

FROM php:8.3-apache as final

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable $_ENV population
RUN sed -i 's/variables_order = "GPCS"/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy vendor and app layers
COPY --from=deps app/vendor/ /var/www/vendor
COPY ./PL /var/www/html
COPY ./BLL /var/www/BLL
COPY ./DAL /var/www/DAL
COPY ./utils /var/www/utils
# ⚠️ Do NOT copy config/.env — set secrets as Azure App Settings instead
COPY ./config/constants.php /var/www/config/constants.php

COPY ./DigiCertGlobalRootG2.crt.pem /var/www/ssl/DigiCertGlobalRootG2.crt.pem

# Enable mod_rewrite and fix document root
RUN a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>' >> /etc/apache2/apache2.conf
    


    
USER www-data
