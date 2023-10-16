#!/bin/bash

if [ -z "$bashdomainname" ]
  then 	
    bashdomainname=$1
fi

echo bashdomainname=$bashdomainname

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ ! -z "$bashdomainname" ]
	then 
		echo -e ${COLOR} Domain name is not provided: Do not install certbot on Centos ${NC}
		exit
fi	

echo -e ${COLOR} Install Snapd ${NC}
cd /usr/local/bin/order-lab/orderflex/
sudo yum install -y snapd
sudo systemctl enable --now snapd.socket
sudo ln -s /var/lib/snapd/snap /snap

echo -e ${COLOR} Install Certbot ${NC}
sudo snap install --classic certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot

echo -e ${COLOR} Get a certificate and have Certbot edit your apache configuration automatically to serve it, turning on HTTPS access in a single step ${NC}
sudo certbot -n --apache --agree-tos --email oli2002@med.cornell.edu --domains "$bashdomainname"

#Result: 
#Successfully received certificate.
#Certificate is saved at: /etc/letsencrypt/live/view.online/fullchain.pem
#Key is saved at:         /etc/letsencrypt/live/view.online/privkey.pem
#This certificate expires on 2024-01-14.
#These files will be updated when the certificate renews.
#Certbot has set up a scheduled task to automatically renew this certificate in the background.
#Deploying certificate
#Successfully deployed certificate for view.online to /etc/httpd/conf.d/000-default-le-ssl.conf

echo -e ${COLOR} Test automatic renewal ${NC}
sudo certbot renew --dry-run

echo -e ${COLOR} Disable the original ssl configuration default-ssl.conf  ${NC}
sudo mv /etc/httpd/conf.d/default-ssl.conf /etc/httpd/conf.d/default-ssl.orig

echo -e ${COLOR} Restart apache server after installing Certbot ${NC}
sudo systemctl restart httpd.service
sudo systemctl status httpd.service


