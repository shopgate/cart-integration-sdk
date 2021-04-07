FROM php:5.6

RUN apt-get update && apt-get install -y git zip && \
    curl -o /composer-setup.php https://getcomposer.org/installer && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer

WORKDIR /app

CMD composer install ; composer run check\&test