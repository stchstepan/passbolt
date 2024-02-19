FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
    && apt-get -y install wget cron git zip unzip php-zip curl ca-certificates net-tools default-mysql-client composer php8.2-cli php8.2-intl php8.2-mbstring php8.2-simplexml php8.2-pdo php8.2-pdo-mysql php8.2-xsl php8.2-phar php8.2-posix php8.2-xml php8.2-ctype php8.2-curl php8.2-gd php-gnupg gnupg php8.2-fpm \
    && apt-get -y install --no-install-recommends nginx \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY passbolt /var/www/passbolt
COPY passbolt.php /var/www/passbolt/config/passbolt.php

RUN rm /var/www/passbolt/config/passbolt.default.php \
    && rm /etc/nginx/sites-enabled/default \
    && sed -i 's/;extension=gd/extension=gd/' /etc/php/8.2/cli/php.ini \
    && composer require psr/simple-cache:^2.0 \
    && composer require maatwebsite/excel \
    && chown -R www-data:www-data /var/www/ \
    && composer update --working-dir=/var/www/passbolt \
    && composer install --no-dev -d /var/www/passbolt \
    && chmod 775 /var/www/passbolt/bin/cake \
    && chown -R www-data:www-data /var/www/passbolt/tmp/ \
    && chmod -R 775 $(find /var/www/passbolt/tmp/ -type d) \
    && chmod -R 664 $(find /var/www/passbolt/tmp/ -type f) \
    && echo "* * * * * /var/www/passbolt/bin/cron >> /var/log/passbolt.log 2>&1" > /etc/cron.d/passbolt-cron \
    && touch /var/log/passbolt.log && chown www-data:www-data /var/log/passbolt.log \
    && crontab -u www-data /etc/cron.d/passbolt-cron \
    && chown www-data:www-data /etc/cron.d/passbolt-cron \
    && chmod 644 /etc/cron.d/passbolt-cron \
    && wget -c https://github.com/nicolas-van/multirun/releases/download/1.1.3/multirun-x86_64-linux-gnu-1.1.3.tar.gz -O - | tar -xz \
    && mv multirun /bin \
    && mkdir -p /var/lib/nginx/body \
    && chown -R www-data:www-data /var/lib/nginx \
    && chown -R www-data:www-data /usr/sbin/nginx \
    && chown -R www-data:www-data /etc/nginx/ \
    && chown -R www-data:www-data /var/log/ \
    && chown www-data:www-data /run/ \
    && chmod 1750 /run/ \
    && chmod gu+s /usr/sbin/cron \
    && chmod -R u+rw /var/log/exim4 \
    && chown -R Debian-exim /var/log/exim4

USER www-data

WORKDIR /var/www/passbolt

EXPOSE 8443
