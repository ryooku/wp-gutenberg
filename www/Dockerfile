FROM cinra/php7:latest

WORKDIR /var/www/html

COPY ./html /var/www/html
COPY ./template/wp-config-sample.php /var/www/html/wp/wp-config.php
COPY ./template/default.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default