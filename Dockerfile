FROM php:8.1-apache

# 安装扩展
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    ca-certificates curl \
    && update-ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli gd \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP配置。保留远程包含练习能力，同时隐藏 PHP 8.1 对该教学开关的启动弃用提示。
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" >> /usr/local/etc/php/conf.d/docker-php-ext.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext.ini \
    && echo "allow_url_include = On" >> /usr/local/etc/php/conf.d/docker-php-ext.ini \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Apache配置
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && chmod 777 /var/www/html/uploads

EXPOSE 80
