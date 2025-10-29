FROM php:8.3-cli

RUN apt-get update && apt-get install -y git zip nano && \
    curl -o /composer-setup.php https://getcomposer.org/installer && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer

WORKDIR /app

CMD tail -f /dev/null
