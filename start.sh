#!/bin/sh

echo "Starting GenZ Auth Server with Supervisord..."

exec /usr/bin/supervisord -c /etc/supervisord.conf
