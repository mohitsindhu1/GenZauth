#!/bin/sh

echo "Starting GenZ Auth Server..."

php-fpm -D

nginx -g 'daemon off;'
