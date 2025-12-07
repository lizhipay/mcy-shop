# syntax=docker/dockerfile:1
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    rsync \
    && rm -rf /var/lib/apt/lists/*

RUN \
    --mount=type=bind,from=ghcr.io/mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions \
    json \
    openssl \
    gd \
    zip \
    curl \
    pdo \ 
    bcmath \
    pdo_mysql

# enable htaccess
RUN a2enmod rewrite

RUN echo "www ALL=(ALL) NOPASSWD: $(pwd)/bin" | tee -a /etc/sudoers

COPY --chmod=0755 docker-entrypoint.sh /usr/bin/docker-entrypoint.sh
COPY --chmod=0755 --chown=www-data:www-data . /opt/mcy-shop-app

VOLUME /var/www/html
EXPOSE 80

ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]
STOPSIGNAL SIGWINCH
CMD ["apache2-foreground"]