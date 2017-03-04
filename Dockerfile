FROM php:5.6.29-apache

ADD httpd/apache2.conf /etc/apache2/apache2.conf
ADD httpd/php.ini /usr/local/etc/php/

RUN  apt-get update && apt-get install -y \
  libssl-dev vim \
  && exiftool \
  && a2enmod rewrite \
  && a2enmod headers \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-install mysql pdo_mysql \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /tmp/pear


