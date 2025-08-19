#!/bin/bash

#To allow this script to run from apache cron job:
#Option 1) Give apache permission to execute systemctl and make key
#Edit the sudoers file to allow apache to run Certbot without a password: sudo visudo -f /etc/sudoers.d/certbot
#Add: apache ALL=(ALL) NOPASSWD: /usr/bin/certbot
#Make it executable by apache:
#sudo chmod +x update-certbot.sh
#sudo chown apache:apache update-certbot.sh
#
#Option 2) Add command to the root cron
#sudo crontab -e
#Add line to run at 2 am EDT (6 am UTC):
# 0 6 * * * /usr/bin/php /srv/order-lab-tenantmanager/orderflex/bin/console cron:certificate-update --env=prod view.online
#
#

#Renew certbot certificate with standalone option:
#1) stop web server: sudo systemctl stop haproxy.service
#2) run: sudo certbot certonly --standalone
#3) create /etc/letsencrypt/live/view.online/fullchain_key.pem containing both your server’s PEM-formatted TLS certificate and its private key
#3a) cd /etc/letsencrypt/live/view.online/
#3b) cat fullchain.pem privkey.pem > fullchain_key.pem
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

#If not expired => Certificate not yet due for renewal; no action taken
echo -e "${COLOR} 2 Run Certbot to Obtain a Certificate ${NC}"
sudo /usr/bin/certbot certonly --standalone --agree-tos --non-interactive --domains "$domainname" --email "$email"

#TODO: Interactive command asking to enter domain name
#echo -e ${COLOR} Generate new certificate ${NC}
#sudo certbot certonly --standalone

#echo -e ${COLOR} cd /etc/letsencrypt/live/view.online/ ${NC}
#cd /etc/letsencrypt/live/view.online/

#It's better to use fullchain.pem: cat fullchain.pem privkey.pem > fullchain_key.pem
echo -e "${COLOR} 3 Create pem certificate ${NC}"
#cat /etc/letsencrypt/live/view.online/fullchain.pem /etc/letsencrypt/live/view.online/privkey.pem > /etc/letsencrypt/live/view.online/fullchain_key.pem
cat /etc/letsencrypt/live/view.online/fullchain.pem /etc/letsencrypt/live/view.online/privkey.pem > /etc/letsencrypt/live/view.online/fullchain_key.pem

echo -e "${COLOR} 4 Start HaProxy ${NC}"
sudo systemctl start haproxy.service

echo -e "${COLOR} End of update-certbot.sh script ${NC}"
exit 0
