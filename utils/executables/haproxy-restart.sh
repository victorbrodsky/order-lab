#!/bin/sh
/usr/bin/sudo /usr/bin/systemctl restart haproxy
sleep 3  # Waits 3 seconds.
#/usr/bin/sudo journalctl -xeu haproxy.service
/usr/bin/sudo service php-fpm restart
sleep 3  # Waits 3 seconds.


