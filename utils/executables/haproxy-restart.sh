#!/bin/sh
/usr/bin/sudo /usr/bin/systemctl restart haproxy
#/usr/bin/sudo journalctl -xeu haproxy.service
/usr/bin/sudo service php-fpm restart


