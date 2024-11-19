#!/bin/sh

php-fpm -D && \
sed -i "s|listen 8080 default_server;|listen ${PORT:-8080} default_server;|g" /etc/nginx/nginx.conf && \
sed -i "s|listen [::]:8080 default_server;|listen [::]:${PORT:-8080} default_server;|g" /etc/nginx/nginx.conf && \
nginx -g "daemon off;"
