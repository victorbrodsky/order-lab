#!/bin/bash

#https://certbot.eff.org/instructions?ws=apache&os=centosrhel8

if [ -z "$bashdomainname" ]
  then 	
    bashdomainname=$1
fi

echo Script install-cerbot.sh: bashdomainname=$bashdomainname

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ ! -z "$bashdomainname" ]
	then 
		echo -e ${COLOR} Script install-cerbot.sh: Install cerbot for domain: "$bashdomainname" ${NC}
	else
		echo -e ${COLOR} Script install-cerbot.sh: Domain name is not provided: Do not install certbot on all OS ${NC}
		exit
fi	

OSNAME=""
if cat /etc/*release | grep ^NAME | grep CentOS; then
    OSNAME="CentOS"
 elif cat /etc/*release | grep ^NAME | grep Red; then
    OSNAME="Red"
 elif cat /etc/*release | grep ^NAME | grep Ubuntu; then
    OSNAME="Ubuntu"
 elif cat /etc/*release | grep ^NAME | grep Alma; then
    OSNAME="Alma"
 else
    echo "OS NOT DETECTED, couldn't install packages"
    exit 1;
 fi
 echo "==============================================="
 echo "Installing packages on $OSNAME"
 echo "==============================================="

echo -e ${COLOR} Script install-cerbot.sh: Disable the original ssl configuration default-ssl.conf  ${NC}
sudo mv /etc/httpd/conf.d/default-ssl.conf /etc/httpd/conf.d/default-ssl.orig

echo -e ${COLOR} Script install-cerbot.sh: Restart apache server before installing Certbot ${NC}
#Ubuntu: sudo systemctl restart apache2.service
if ["$OSNAME" = "Ubuntu"] 
	then
		echo "==============================================="
		echo "Restart Apache on Ubuntu $OSNAME"
		echo "==============================================="
		sudo systemctl restart apache2.service
		sudo systemctl status apache2.service
	else	
		echo "==============================================="
		echo "Restart Apache on all others OS $OSNAME"
		echo "==============================================="
		sudo systemctl restart httpd.service
		sudo systemctl status httpd.service
fi


echo -e ${COLOR} Script install-cerbot.sh: Install Snapd ${NC}
cd /usr/local/bin/order-lab/orderflex/

echo -e ${COLOR} Script install-cerbot.sh: Install Snapd according to OS ${NC}
#Centos yum, Alma dnf, Ubuntu apt

#OSNAME = 'grep -E '^(NAME)=' /etc/os-release'
#if [ "$OSNAME" =~ ^CentOS ]
#	then
#		sudo yum install -y snapd
#if	
#if [ "$OSNAME" =~ ^Alma ]
#	then
#		sudo dnf install -y snapd
#if
#if [ "$OSNAME" =~ ^Ubuntu ]
#	then
#		sudo apt install -y snapd
#if

#https://stackoverflow.com/questions/394230/how-to-detect-the-os-from-a-bash-script
YUM_PACKAGE_NAME = "snapd"
if ["$OSNAME" = "CentOS"] then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on CentOS"
	echo "==============================================="
    sudo yum install -y $YUM_PACKAGE_NAME
elif ["$OSNAME" = "Red"] then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on RedHat"
	echo "==============================================="
    sudo yum install -y $YUM_PACKAGE_NAME
elif ["$OSNAME" = "Ubuntu"] then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Ubuntu"
    echo "==============================================="
    #sudo apt-get update
    sudo apt-get install -y $YUM_PACKAGE_NAME
elif ["$OSNAME" = "Alma"] then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Alma"
    echo "==============================================="
    sudo dnf install -y $YUM_PACKAGE_NAME
else
    echo "OS NOT DETECTED, couldn't install package $YUM_PACKAGE_NAME"
    exit 1;
fi


echo -e ${COLOR} Script install-cerbot.sh: Enable and create symlink for Snapd ${NC}
sudo systemctl enable --now snapd.socket
sudo ln -s /var/lib/snapd/snap /snap

echo ""
sleep 3

echo -e ${COLOR} Script install-cerbot.sh: Install Certbot ${NC}
sudo snap install --classic certbot

echo ""
sleep 3

#sudo snap install --classic certbot => error: too early for operation, device not yet seeded or device model not acknowledged
echo -e ${COLOR} Script install-cerbot.sh: Install Certbot second attempt ${NC}
sudo snap install --classic certbot

echo -e ${COLOR} Script install-cerbot.sh: create symbolik link ${NC}
sudo ln -s /snap/bin/certbot /usr/bin/certbot

echo -e ${COLOR} Script install-cerbot.sh: Get a certificate and have Certbot edit your apache configuration automatically ${NC}
echo -e ${COLOR} Script install-cerbot.sh: sudo certbot -n -v --apache --agree-tos --email oli2002@med.cornell.edu --domains "$bashdomainname" ${NC}
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

echo -e ${COLOR} Script install-cerbot.sh: Test automatic renewal ${NC}
sudo certbot renew --dry-run

echo -e ${COLOR} Script install-cerbot.sh: Restart apache server after installing Certbot ${NC}
if ["$OSNAME" = "Ubuntu"] then
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
else	
	sudo systemctl restart httpd.service
	sudo systemctl status httpd.service
fi


