#!/bin/bash

#https://certbot.eff.org/instructions?ws=apache&os=centosrhel7

if [ -z "$bashdomainname" ]
  then 	
    bashdomainname=$1
fi

echo install-cerbot bash script: bashdomainname=$bashdomainname

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ ! -z "$bashdomainname" ]
	then 
		echo -e ${COLOR} install-cerbot bash script: Install cerbot for domain: "$bashdomainname" ${NC}
	else
		echo -e ${COLOR} install-cerbot bash script: Domain name is not provided: Do not install certbot on Centos ${NC}
		exit
fi	

echo -e ${COLOR} install-cerbot bash script: Disable the original ssl configuration default-ssl.conf  ${NC}
sudo mv /etc/httpd/conf.d/default-ssl.conf /etc/httpd/conf.d/default-ssl.orig

echo -e ${COLOR} install-cerbot bash script: Install Snapd ${NC}
cd /usr/local/bin/order-lab/orderflex/
sudo yum install -y snapd
sudo systemctl enable --now snapd.socket
sudo ln -s /var/lib/snapd/snap /snap

echo ""
sleep 3

echo -e ${COLOR} install-cerbot bash script: Install Certbot ${NC}
sudo snap install --classic certbot

echo ""
sleep 3

#sudo snap install --classic certbot => error: too early for operation, device not yet seeded or device model not acknowledged
echo -e ${COLOR} install-cerbot bash script: Install Certbot second attempt ${NC}
sudo snap install --classic certbot

echo -e ${COLOR} install-cerbot bash script: create symbolik link ${NC}
sudo ln -s /snap/bin/certbot /usr/bin/certbot

echo -e ${COLOR} install-cerbot bash script: Get a certificate and have Certbot edit your apache configuration automatically ${NC}
echo -e ${COLOR} install-cerbot bash script: sudo certbot -n -v --apache --agree-tos --email oli2002@med.cornell.edu --domains "$bashdomainname" ${NC}
sudo certbot -n -v --apache --agree-tos --email oli2002@med.cornell.edu --domains "$bashdomainname"

#Result: success
#Successfully received certificate.
#Certificate is saved at: /etc/letsencrypt/live/view.online/fullchain.pem
#Key is saved at:         /etc/letsencrypt/live/view.online/privkey.pem
#This certificate expires on 2024-01-14.
#These files will be updated when the certificate renews.
#Certbot has set up a scheduled task to automatically renew this certificate in the background.
#Deploying certificate
#Successfully deployed certificate for view.online to /etc/httpd/conf.d/000-default-le-ssl.conf

#Result: error
#[root@packer-1697489156 ~]# sudo certbot -n --apache --agree-tos --email oli2002@med.cornell.edu --domains view.online
#Saving debug log to /var/log/letsencrypt/letsencrypt.log
#Requesting a certificate for view.online
#
#Certbot failed to authenticate some domains (authenticator: apache). The Certificate Authority reported these problems:
#  Domain: view.online
#  Type:   unauthorized
#  Detail: 161.35.176.72: Invalid response from https://view.online/.well-known/acme-challenge/905jWgQcz1L8L1QDFCGDbjOHMa9MvIBVABQDZzsvUR4: 404
#
#Hint: The Certificate Authority failed to verify the temporary Apache configuration changes made by Certbot. Ensure that the listed domains point to this Apache server and that it is accessible from the internet.

#Some challenges have failed.
#Ask for help or search for solutions at https://community.letsencrypt.org. See the logfile /var/log/letsencrypt/letsencrypt.log or re-run Certbot with -v for more details.

echo -e ${COLOR} install-cerbot bash script: Test automatic renewal ${NC}
sudo certbot renew --dry-run

echo -e ${COLOR} install-cerbot bash script: Restart apache server after installing Certbot ${NC}
sudo systemctl restart httpd.service
sudo systemctl status httpd.service


