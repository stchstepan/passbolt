#!/bin/bash

if [ ! -f /var/www/passbolt/config/gpg/serverkey_private.asc ] && [ ! -f /var/www/passbolt/config/gpg/serverkey.asc ]; then
    sh /generate_key.sh

    gpg --armor --export-secret-keys your_mail@your.org > /var/www/passbolt/config/gpg/serverkey_private.asc
    gpg --armor --export your_mail@your.org > /var/www/passbolt/config/gpg/serverkey.asc
    sed -i "s/'fingerprint' => '',/'fingerprint' => '$(gpg --list-keys | awk 'NR==4' | tr -d ' ')',/g" /var/www/passbolt/config/passbolt.php
    /var/www/passbolt/bin/cake passbolt create_jwt_keys
fi

/var/www/passbolt/bin/cake passbolt migrate --no-clear-cache
multirun "php-fpm8.2 -F" "/usr/sbin/nginx -g 'daemon off;'" "cron -f"