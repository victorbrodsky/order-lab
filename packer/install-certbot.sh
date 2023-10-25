#!/bin/bash

#https://certbot.eff.org/instructions?ws=apache&os=centosrhel8
#bash /usr/local/bin/order-lab/packer/install-certbot.sh view.online installcertbot oli2002@med.cornell.edu apitoken

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#sudo snap install doctl

if [ -z "$domainname" ]
  then 	
    domainname=$1
fi

if [ -z "$sslcertificate" ]
  then
    sslcertificate=$2
fi

if [ -z "$email" ]
  then
    email=$3
fi

if [ -z "$apitoken" ]
  then
    apitoken=$4
fi

echo domainname=$domainname
echo sslcertificate=$sslcertificate
echo email=$email
echo apitoken=$apitoken

echo Script install-cerbot.sh: domainname=$domainname

if [ "$sslcertificate" != "installcertbot" ]
  then
    echo "Abort certbot installation: sslcertificate option is not 'installcertbot'"
    exit 0
fi

if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ] ]
  then
    #email='myemail@myemail.com'
    echo "Error: email is not provided for installcertbot option"
    echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
    exit 0
fi

if [ ! -z "$domainname" ]
	then 
		echo -e ${COLOR} Script install-cerbot.sh: Install cerbot for domain: "$domainname" ${NC}
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
if [ "$OSNAME" = "Ubuntu" ]; 
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
#https://stackoverflow.com/questions/394230/how-to-detect-the-os-from-a-bash-script
YUM_PACKAGE_NAME="snapd"
if [ "$OSNAME" = "CentOS" ] 
then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on CentOS"
	echo "==============================================="
    sudo yum install -y "$YUM_PACKAGE_NAME"
    #sudo yum install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Red" ] 
then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on RedHat"
	echo "==============================================="
    sudo yum install -y "$YUM_PACKAGE_NAME"
    #sudo yum install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Ubuntu" ] 
then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Ubuntu"
    echo "==============================================="
    #sudo apt-get update
    sudo apt-get install -y "$YUM_PACKAGE_NAME"
    #sudo apt install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Alma" ] 
then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Alma"
    echo "==============================================="
    sudo dnf install -y "$YUM_PACKAGE_NAME"
    #sudo dnf install -y python3-certbot-dns-digitalocean
else
    echo "OS NOT DETECTED, couldn't install package $YUM_PACKAGE_NAME"
    exit 1;
fi

############### Install doctl and create droplet from image ###############
#TODO: https://docs.digitalocean.com/reference/doctl/how-to/install/
echo -e ${COLOR} Script install-cerbot.sh: Install doctl ${NC}
#sudo snap install doctl
cd ~
wget https://github.com/digitalocean/doctl/releases/download/v1.100.0/doctl-1.100.0-linux-amd64.tar.gz
tar xf ~/doctl-1.100.0-linux-amd64.tar.gz
sudo mv ~/doctl /usr/local/bin

#1) doctl auth init --access-token $apitoken
doctl auth init --access-token $apitoken

#2) doctl compute domain records create $domainname --record-type A --record-name @ --record-ttl 60 --record-data $DROPLETIP -v
#doctl compute domain records create view.online --record-type A --record-name @ --record-ttl 60 --record-data 142.93.65.236 -v
#DROPLETIP=$(ip -o route get to 8.8.8.8 | sed -n 's/.*src \([0-9.]\+\).*/\1/p')
#echo -e ${COLOR} Script install-cerbot.sh: DROPLETIP="$DROPLETIP" ${NC}

#IMAGENAME='packer-1698102450' IMAGEID=142936498
echo -e ${COLOR} *** Building VM droplet from image *** ${NC}
LASTLINE=$(doctl compute image list --public | tail -n1) #get the last line of the public images
echo "LASTLINE=$LASTLINE"

LASTLINEINFO=( $LASTLINE )

echo -e ${COLOR} *** Getting the first IMAGEID and the second IMAGENAME elements from LASTLINE *** ${NC}
IMAGEID="${LASTLINEINFO[0]}"
IMAGENAME="${LASTLINEINFO[1]}"
echo -e ${COLOR} IMAGEID="$IMAGEID", IMAGENAME="$IMAGENAME" ${NC}

echo -e ${COLOR} *** Creating droplet IMAGENAME=$IMAGENAME, IMAGEID=$IMAGEID ... *** ${NC}
DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)

dropletinfos=( $DROPLET )
DROPLETIP="${dropletinfos[2]}"
echo "droplet IP=$DROPLETIP"
############### EOF Install doctl and create droplet from image ###############

#doctl compute domain records create "$domainname" --record-type A --record-name @ --record-ttl 60 --record-data "$DROPLETIP" -v
########## Create domain ###########
echo "Before creating domainname=$domainname"
if [ ! -z "$domainname" ] && [ "$domainname" != "domainname" ]
  then
    #0) check and create domain and DNS
    echo "Create domain domainname=$domainname"
    DOMAINCHECK=$(doctl compute domain get $domainname)
    echo "Check if domain $domainname exists: $DOMAINCHECK"
    if [ -z "$DOMAINCHECK" ]
      then
        echo "Create domain domainname=$domainname"
        DOMAINRES=$(doctl compute domain create $domainname --ip-address $DROPLETIP)
        echo "Created domain DOMAINRES=$DOMAINRES"
    fi

    #check and delete existing domain DNS's A records with record 'www' or '@'
    #1) doctl compute domain records list $domainname
    LIST=$(doctl compute domain records list $domainname | grep -e '@' -e 'www' | grep -w A | awk '{print $1}')
    #listinfo=( $LIST )
    #RECORDID="${listinfo[0]}"

    #2) doctl compute domain records delete $domainname record_id. --force - Delete record without confirmation prompt
    for recordid in $LIST; do
      echo "Delete old DNS record ID=$recordid"
      DELETERES=$(doctl compute domain records delete $domainname $recordid --force -v)
      #echo "DELETERES=$DELETERES"
    done

    #doctl compute domain create domain_name --ip-address droplet_ip_address
    #'--record-name www' will create domain name with www prefix, i.e. www.view.online
    #'--record-name @' will create domain name without prefix, i.e. view.online
    #doctl compute domain records create $domainname --record-type A --record-name www --record data $DROPLETIP --record-ttl 30 -v
    #https://docs.digitalocean.com/reference/doctl/reference/compute/domain/records/update/
    #'doctl compute domain records create' or 'doctl compute domain records update': --record-ttl 	The record’s Time To Live value, in seconds, default: 1800
    DOMAIN=$(doctl compute domain records create $domainname --record-type A --record-name @ --record-ttl 60 --record-data $DROPLETIP -v)
    echo "DOMAIN=$DOMAIN"
    DROPLETIP="$domainname"
  else
	  echo "Do not create domain domainname=$domainname"
fi

echo -e ${COLOR} Sleep 180 seconds after creating domain "$domainname" with IP "$DROPLETIP" ${NC}
sleep 180
########## EOF Create domain ###########

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

echo -e ${COLOR} Sleep 180 seconds before installing certbot on Apache ${NC}
sleep 180

echo -e ${COLOR} Script install-cerbot.sh: Get a certificate and have Certbot edit your apache configuration automatically ${NC}
echo -e ${COLOR} Script install-cerbot.sh: sudo certbot -n -v --apache --agree-tos --email "$email" --domains "$domainname" ${NC}
sudo certbot -n -v --apache --agree-tos --email "$email" --domains "$domainname"
#sudo certbot -n -v --apache --agree-tos --dns-digitalocean --email "$email" --domains "$domainname"

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
if [ "$OSNAME" = "Ubuntu" ] 
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


