FROM php:5.6.29-apache

ADD php.ini /usr/local/etc/php/

RUN  apt-get update && apt-get install -y \
  libssl-dev \
  vim \
  calibre-bin \
  supervisor \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-install mysql pdo_mysql \
    && apt-get autoremove -y \
    && apt-get clean

CMD ["supervisord", "-c", "/var/www/html/etc/supervisord/supervisord.conf"]