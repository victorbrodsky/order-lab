#!/bin/sh

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"
case $key in
	-m|--multitenant)
		multitenant="$2"
		shift # past argument
		shift # past value
    ;;
    *)    # unknown option
		POSITIONAL+=("$1") # save it in an array for later
		shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

echo multitenant=$multitenant

sleep 1  # Waits 1 seconds.
/usr/bin/sudo /usr/bin/systemctl restart haproxy
sleep 1  # Waits 1 seconds.
#/usr/bin/sudo journalctl -xeu haproxy.service
/usr/bin/sudo service php-fpm restart
sleep 1  # Waits 1 seconds.


