FROM wordpress:7.0.4-php8.3-apache

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
    && apt-get install --yes --no-install-recommends msmtp-mta \
    && rm -rf /var/lib/apt/lists/*

COPY docker/wordpress/msmtprc /etc/msmtprc
COPY docker/wordpress/mailpit.ini /usr/local/etc/php/conf.d/estatein-mailpit.ini

RUN chmod 0644 /etc/msmtprc /usr/local/etc/php/conf.d/estatein-mailpit.ini
