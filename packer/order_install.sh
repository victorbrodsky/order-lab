#!/bin/bash

#Standalone:
#1) Install git
#	sudo dnf update -y
#	sudo dnf install git
#2) git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
#3) cd /usr/local/bin/order-lab/packer
#4) bash order_install.sh symfony symfony alma9_install.sh | tee install.log

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#bashdbuser=$1
#bashdbpass=$2
#os=$3

if [ -z "$bashdbuser" ]
  then 	
    bashdbuser=$1
fi
if [ -z "$bashdbpass" ]
  then 	
    bashdbpass=$2
fi
if [ -z "$os" ]
  then 	
    os=$3
fi

if [ -f "$bashdbuser" ]; then
    bashdbuser='symfony'
fi
if [ -f "$bashdbpass" ]; then
    bashdbpass='symfony'
fi
if [ -f "$os" ]; then
    os='alma9_install.sh'
fi

echo bashdbuser=$bashdbuser
echo bashdbpass=$bashdbpass
echo os=$os

#Install OS, Apache, PHP, DB, Utils, Python, Order, Prepare
echo -e ${COLOR} Install OS, Apache, PHP, DB, Utils, Order ... ${NC}
#/bin/bash /usr/local/bin/order-lab/packer/"$os" "$bashdbuser" "$bashdbpass"

echo -e ${COLOR} Check OS, Apache, PHP, DB ${NC}
sudo hostnamectl
sudo systemctl status httpd.service --no-pager		   
psql --version
php -version

echo -e ${COLOR} Copy parameters file parameters.yml to /usr/local/bin/order-lab/orderflex/config/ ${NC}
cp /usr/local/bin/order-lab/packer/parameters.yml /usr/local/bin/order-lab/orderflex/config/

echo -e ${COLOR} Replace user and password for parameters.yml ${NC}
sed -i -e "s/bash_dbuser/$bashdbuser/g" /usr/local/bin/order-lab/orderflex/config/parameters.yml
sed -i -e "s/bash_dbpass/$bashdbpass/g" /usr/local/bin/order-lab/orderflex/config/parameters.yml

echo -e ${COLOR} Create ssl ${NC}
sudo mkdir /usr/local/bin/order-lab/ssl/
cp /usr/local/bin/order-lab/packer/localhost.crt /usr/local/bin/order-lab/ssl/apache2.crt
cp /usr/local/bin/order-lab/packer/localhost.key /usr/local/bin/order-lab/ssl/apache2.key

echo -e ${COLOR} Composer and deploy using orderflex ${NC}
cd /usr/local/bin/order-lab/orderflex					
composer self-update
composer install

echo -e ${COLOR} Install frozen-lockfile ${NC}
sudo yarn install --frozen-lockfile

echo -e ${COLOR} Deploy ${NC}
sudo chmod +x /usr/local/bin/order-lab/orderflex/deploy_prod.sh
bash /usr/local/bin/order-lab/orderflex/deploy_prod.sh -withdb
#sudo chown -R apache:apache /usr/local/bin/order-lab
#sudo chown -R apache:apache /usr/local/bin/order-lab/.git/

#Open init http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/





