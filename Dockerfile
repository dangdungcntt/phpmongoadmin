FROM dangdungcntt/php:8.0-nginx

RUN install-php-extensions mongodb

COPY composer.json composer.json

COPY composer.lock composer.lock

RUN composer install --prefer-dist --no-progress --no-scripts --no-autoloader && rm -rf /root/.composer

COPY . /home/app

RUN cp .env.production .env \
    && chown -R www-data:www-data storage bootstrap \
    && composer dump-autoload --no-scripts --optimize
