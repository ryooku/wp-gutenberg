FROM cinra/php56

WORKDIR /var/www/html

COPY ./html /var/www/html
COPY ./template/wp-config-sample.php /var/www/html/wp/wp-config.php
COPY ./template/default.conf /etc/apache2/sites-available/

RUN a2ensite default