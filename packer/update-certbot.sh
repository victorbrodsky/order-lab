#!/bin/bash

#Renew certbot certificate with standalone option:
#1) stop web server: sudo systemctl stop haproxy.service
#2) run: sudo certbot certonly --standalone
#3) create /etc/letsencrypt/live/view.online/cert_key.pem containing both your server’s PEM-formatted TLS certificate and its private key
#3a) cd /etc/letsencrypt/live/view.online/
#3b) cat cert.pem privkey.pem > cert_key.pem
#4) start haproxy: sudo systemctl start haproxy.service

if [ -z "$domainname" ]
  then
    domainname=$1
fi

if [ -z "$email" ]
  then
    email=$2
fi

echo domainname=$domainname
echo email=$email

COLOR='\033[1;36m'
NC='\033[0m' # No Color

echo -e ${COLOR} Update SSL certificate via certbot and HaProxy ${NC}

echo -e "${COLOR} 1 Stop HAProxy Temporarily ${NC}"
sudo systemctl stop haproxy.service

echo -e "${COLOR} 2 Run Certbot to Obtain a Certificate ${NC}"
sudo certbot certonly --standalone --agree-tos --non-interactive --email "$email" --domains "$domainname"

#TODO: Interactive command asking to enter domain name
echo -e ${COLOR} Generate new certificate ${NC}
sudo certbot certonly --standalone

#echo -e ${COLOR} cd /etc/letsencrypt/live/view.online/ ${NC}
#cd /etc/letsencrypt/live/view.online/

echo -e ${COLOR} Create pem certificate ${NC}
cat /etc/letsencrypt/live/view.online/cert.pem /etc/letsencrypt/live/view.online/privkey.pem > /etc/letsencrypt/live/view.online/cert_key.pem

echo -e ${COLOR} Start HaProxy ${NC}
sudo systemctl start haproxy.service

echo -e ${COLOR} End of update-certbot.sh script ${NC}
exit 0
