#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)
echo @### Get bash parameters ###
if [ -z "$bashdbuser" ]
  then
    bashdbuser=$1
fi
if [ -z "$bashdbpass" ]
  then
    bashdbpass=$2
fi
if [ -z "$bashprotocol" ]
  then
    bashprotocol=$3
fi
if [ -z "$bashdomainname" ]
  then
    bashdomainname=$4
fi
if [ -z "$bashsslcertificate" ]
  then
    bashsslcertificate=$5
fi
if [ -z "$bashemail" ]
  then
    bashemail=$6
fi

echo bashdbuser=$bashdbuser
echo bashdbpass=$bashdbpass
echo bashprotocol=$bashprotocol
echo bashdomainname=$bashdomainname
echo bashsslcertificate=$bashsslcertificate
echo bashemail=$bashemail

#WHITE='\033[1;37m'
COLOR='\033[1;36m'
NC='\033[0m' # No Color

# Function update os
f_update_os () {
    echo -e ${COLOR} Starting update os ubuntu 22 ... ${NC}
	
	echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
	
	sleep 1

	echo -e ${COLOR} Ubuntu 22 update ${NC}
    #sudo apt update
	sudo apt-get update && apt-get install -y apt-transport-https
	
	#echo -e ${COLOR} sudo apt -y full-upgrade ${NC}
	#sudo apt -y full-upgrade
	
	#echo -e ${COLOR} Ubuntu 22 system reboot  ${NC}
	#[ -f /var/run/reboot-required ] && sudo reboot -f
	#sudo reboot -f
	#sudo systemctl reboot
	#sudo shutdown -r now
	
	echo -e ${COLOR} Ubuntu 22 Install the necessary packages  ${NC}
	sudo apt install -y vim curl wget gpg gnupg2 software-properties-common apt-transport-https lsb-release ca-certificates
	
    echo ""
    sleep 1
}

# Function install LAMP stack
f_install_apache () {
    ########## INSTALL APACHE ##########
    echo -e ${COLOR} Ubuntu 22 install apache ${NC}
	sudo apt install -y apache2

	#sudo apt install -y httpd
	echo -e  ${COLOR} install mod_ssl ${NC}
	#sudo apt install -y mod_ssl
	sudo a2enmod ssl
	
	echo -e ${COLOR} List the ufw application profiles ${NC}
	sudo ufw app list
	
	echo -e ${COLOR} List the ufw application profiles ${NC}
	sudo ufw allow 'Apache'
	sudo ufw status
	
	echo -e ${COLOR} Install mod_rewrite module ${NC}
	sudo a2enmod rewrite
	
	echo -e ${COLOR} Make sure the service is active ${NC}
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
	#sudo journalctl -xeu apache2.service
	
	echo ""
    sleep 1
}

f_install_postgresql15 () {
    ########## INSTALL Postgresql ##########
    echo -e "${COLOR} Installing Postgresql 15 ... ${NC}"
    sleep 1

	echo -e ${COLOR} import GPG key used in signing packages ${NC}		
	curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc|sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/postgresql.gpg
	
	echo -e ${COLOR} Add the PostgreSQL repository ${NC}	
	sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
	
	echo -e ${COLOR} Inform the system about the newly added repository ${NC}
	sudo apt update
	
	#echo -e ${COLOR} Repo metadata sync should be successful for newly added repository ${NC}
	#Hit:1 http://apt.postgresql.org/pub/repos/apt jammy-pgdg InRelease
	
	echo -e ${COLOR} install PostgreSQL 15 ${NC}
	sudo apt install -y postgresql
	
	echo @### Restart and enable PostgreSQL ###	
	sudo systemctl restart postgresql
	sudo systemctl enable postgresql
	
	echo @### Check install version of PostgreSQL ###	
	#sudo -u postgres psql -c "SELECT version();"
	psql --version

	#echo @### Check install version of PostgreSQL ###	
	#sudo -u postgres psql

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
		
	echo @### Create system DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorderSystem
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorderSystem to $bashdbuser"	
		
	#Modify pg_hba.conf in /var/lib/pgsql/15/data to replace "ident" to "md5"
	echo -e ${COLOR} Modify pg_hba.conf in /etc/postgresql/15/main to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /etc/postgresql/15/main/pg_hba.conf
	
	echo -e ${COLOR} Modify pg_hba.conf ident to md5 ${NC}
	sed -i -e "s/ident/md5/g" /etc/postgresql/15/main/pg_hba.conf
	
	#echo -e ${COLOR} Add TEXTTOEND to pg_hba.conf ${NC}
	sed -i -e "\$aTEXTTOEND" /etc/postgresql/15/main/pg_hba.conf
	
	#echo -e ${COLOR} Replace TEXTTOEND in pg_hba.conf ${NC}
	sed -i "s/TEXTTOEND/host all all 0.0.0.0\/0 md5/g" /etc/postgresql/15/main/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /etc/postgresql/15/main/postgresql.conf
	
	echo -e ${COLOR} Set port ${NC}
	sed -i -e "s/#port/port = 5432 #port/g" /etc/postgresql/15/main/postgresql.conf	
		
		
	sudo systemctl restart postgresql
	sudo systemctl enable postgresql
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
	
	echo ""
    sleep 1
}

f_install_php82 () {
	#https://www.kubuntuforums.net/forum/currently-supported-releases/kubuntu-22-10/software-support-be/668281-php-8-2-cannot-be-installed-on-kubuntu-22-10
	#php 8.2 cannot be installed on Kubuntu 22.10. Use ubuntu-22-04-x64 
	
    ########## INSTALL APACHE 8.2 ##########
    echo "Installing apache 8.2"
    sleep 1

	echo @### Install required dependencies ###
	sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common

	echo @### Set up PHP repository ###
	sudo add-apt-repository ppa:ondrej/php -y


	echo @### PHP2: sudo update ###
	sudo apt -y update
	
	#mcrypt moved to PECL https://computingforgeeks.com/install-php-mcrypt-extension-on-ubuntu/
	#echo @### Install Development tools on Ubuntu ###
	#sudo apt install -y build-essential

	#echo @### PHP3: Search for PHP 8.1 packages ###
	#sudo yum search php81 | more
	#sudo yum search php81 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP3: Install PHP 8.2 ###
	sudo apt install -y php8.2 
	sudo apt install -y php8.2-pear php8.2-dev libmcrypt-dev
	
	#echo @### Update PECL channels ###
	#sudo pecl channel-update pecl.php.net
	#sudo pecl update-channels
	
	echo @### PHP: list of all the installable PHP modules ###
	php -m
	
	echo @### PHP: Install PHP modules ###
	#sudo apt install -y php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	#sudo apt install -y php8.2-{cli,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	#json - Package 'php8.2-json' has no installation candidate
	sudo apt install -y php8.2-{cli,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml}
	#sudo apt install -y php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	#sudo apt install -y php8.2-{pgsql,xmlreader,pdo,dom,intl,pear,bcmath,common}
	#Unable to locate package php8.2-pear, Couldn't find any package by glob 'php8.2-pear'
	sudo apt install -y php8.2-{pgsql,xmlreader,pdo,dom,intl,bcmath,common}
	sudo apt-get install -y php-pear
	sudo apt-get install -y php-json
	
	#echo @### PHP: Install mcrypt ###
	#"\n" | sudo pecl install mcrypt
	
	#sudo apt install -y php-syspaths
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart postgresql
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
	
	echo ""
    sleep 1
}
f_install_php81 () {
	#Remove php: sudo apt remove --autoremove php8.1 libapache2-mod-php8.1 php8.1-*
    ########## INSTALL APACHE 8.1 ##########
    echo "Installing apache 8.2, but only php 8.1.7 available now ..."
    sleep 1

	echo @### Install required dependencies ###
	sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common

	echo @### Set up PHP repository ###
	sudo add-apt-repository ppa:ondrej/php


	echo @### PHP2: sudo update ###
	sudo apt -y update
	
	#mcrypt moved to PECL https://computingforgeeks.com/install-php-mcrypt-extension-on-ubuntu/
	#echo @### Install Development tools on Ubuntu ###
	#sudo apt install -y build-essential

	#echo @### PHP3: Search for PHP 8.1 packages ###
	#sudo yum search php81 | more
	#sudo yum search php81 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP3: Install PHP ###
	sudo apt install -y php php-pear php-dev libmcrypt-dev
	
	#echo @### Update PECL channels ###
	#sudo pecl channel-update pecl.php.net
	#sudo pecl update-channels
	
	echo @### PHP: list of all the installable PHP modules ###
	php -m
	
	echo @### PHP: Install PHP modules ###
	#sudo apt install -y php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	sudo apt install -y php-{cli,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	#sudo apt install -y php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	sudo apt install -y php-{pgsql,xmlreader,pdo,dom,intl,pear,bcmath,common}
	
	#echo @### PHP: Install mcrypt ###
	#"\n" | sudo pecl install mcrypt
	
	#sudo apt install -y php-syspaths
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart postgresql
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
	
	echo ""
    sleep 1
}

f_install_util () {
    ########## INSTALL UTILITIES ##########
    echo "Installing util ..."
    sleep 1

	echo -e ${COLOR} Install Git ${NC}		
	sudo apt install -y git	
	
	echo -e ${COLOR} Install libreoffice, ghostscript, pdftk ${NC}
	#sudo apt update
	#disable for testing: sudo apt install -y xvfb libfontconfig	
	sudo apt install -y libreoffice	
	sudo apt install -y ghostscript
	sudo apt install -y pdftk  

	sudo apt install -y wget unzip
	
	#echo -e ${COLOR} Install wkhtmltopdf dependencies xorg-x11-fonts-75dpi and xorg-x11-fonts-Type1 ${NC}
	#apt install -y xorg-x11-fonts-75dpi
	#apt install -y xorg-x11-fonts-Type1
	#apt install xz
	
	#echo -e ${COLOR} synchronize the rpm & yumdb databases ${NC}
	#apt history sync
	
	echo -e ${COLOR} Install wkhtmltopdf ${NC}
	#wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.5/wkhtmltox-0.12.5-1.centos7.x86_64.rpm
	#sudo rpm -Uvh wkhtmltox-0.12.5-1.centos7.x86_64.rpm
	sudo apt install -y wkhtmltopdf
	
	#echo -e ${COLOR} Install xorg-x11-server-Xvfb ${NC}
	#sudo apt install -y xorg-x11-server-Xvfb
	
	echo -e ${COLOR} Get version wkhtmltopdf ${NC}
	#/usr/bin/xvfb-run wkhtmltopdf --version
	wkhtmltopdf --version
	
	#http://bashworkz.com/installing-pdftk-on-centos-5-and-6-pdf-management-utility-tool/
	#echo -e ${COLOR} Install pdftk ${NC}
	#sudo apt install -y libgcj
	#apt install -y https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/pdftk-2.02-1.el6.x86_64.rpm
	#pdftk --help | more
	
	#https://github.com/documentcloud/docsplit/issues/123
	#echo -e ${COLOR} Install pdftk ${NC}
	#sudo wget https://copr.fedorainfracloud.org/coprs/robert/gcj/repo/epel-7/robert-gcj-epel-7.repo -P /etc/yum.repos.d
	#sudo wget https://copr.fedorainfracloud.org/coprs/robert/pdftk/repo/epel-7/robert-pdftk-epel-7.repo -P /etc/yum.repos.d
	#sudo apt install -y pdftk
	
	#http://www.vassox.com/linux-general/installing-phantomjs-on-centos-7-rhel/
	echo -e ${COLOR} Install PhantomJS ${NC}
	sudo apt install -y dnf
	#sudo dnf install -y glibc fontconfig
	sudo apt install -y lbzip2
	sudo apt install -y fontconfig
	#sudo apt install -y freetype
	sudo apt install -y wget
	sudo apt install -y bzip2
	cd /opt
	sudo wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2
	sudo tar -xvf phantomjs-2.1.1-linux-x86_64.tar.bz2
	ln -s /opt/phantomjs-2.1.1-linux-x86_64/bin/phantomjs /usr/local/bin/phantomjs phantomjs --version
	
	#Install pdftotext: https://github.com/spatie/pdf-to-text
	sudo apt install -y poppler-utils
	
	echo -e ${COLOR} Install Nodejs ${NC}
	cd ~
	curl -sL https://deb.nodesource.com/setup_18.x -o nodesource_setup.sh
	sudo bash nodesource_setup.sh
	echo -e ${COLOR} Autoremove no longer required packages libc-ares2 libjs-highlight.js libnode72 ${NC}
	sudo apt autoremove -y
	sudo apt install nodejs -y
	echo -e ${COLOR} Check version Nodejs ${NC}
	node -v
	
	#Something wrong with yarn --version
	#Expected version ">=14". Got "12.22.12"
	#error react-router-dom@6.4.5: The engine "node" is incompatible with this module. Expected version ">=14". Got "12.22.9"
	echo -e ${COLOR} Install Yarn ${NC}
	#curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo
	#curl --silent --location https://rpm.nodesource.com/setup_16.x | sudo bash -
	curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
	echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
	sudo apt update
	sudo apt install -y yarn
	echo -e ${COLOR} Yarn version ${NC}
	yarn --version
	echo -e ${COLOR} Nodejs version ${NC}
	node -v
	
	echo -e ${COLOR} Append lsl alias to ~/.bashrc ${NC}
	echo 'alias lsl="ls -lrt"' >> ~/.bashrc
	source ~/.bashrc
	
	#If above Nodejs is not working:
	#sudo apt remove libnode72
	#sudo apt remove libnode-dev
	#sudo dpkg --remove --force-remove-reinstreq libnode-dev
	#sudo dpkg --remove --force-remove-reinstreq libnode72:amd64
	#sudo dpkg -i --force-overwrite /var/cache/apt/archives/nodejs_*
	#sudo apt -f install
	#sudo apt update
	#sudo apt dist-upgrade
	#curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
	#sudo apt-get install nodejs
	
	echo ""
    sleep 1
}

f_install_python3 () {
    ########## INSTALL PYTHON for server health monitor ##########
	echo -e "${COLOR} Installing python3 ..."
	sudo apt install -y python3
	sudo apt install -y python3-pip
	sudo pip3 install requests
	python3 -V
}

f_install_order () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Installing order ..."
    sleep 1
	
	echo @### Install Git ###		
	sudo apt install -y git	

	echo -e ${COLOR} Clone ORDER and copy config and php.ini files, install composer ${NC}
	ssh-keyscan github.com >> ~/.ssh/known_hosts
	cd /usr/local/bin/
	git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
	
	echo -e ${COLOR} List ORDER folder after clone ${NC}
	ls /usr/local/bin/order-lab
	
	echo -e ${COLOR} To prevent: detected dubious ownership in repository at /usr/local/bin/order-lab ${NC}
	git config --global --add safe.directory /usr/local/bin/order-lab
	
	#chown -R www-data:www-data /var/www
	echo -e ${COLOR} sudo chmod a+x /usr/local/bin/order-lab ${NC}
	sudo chmod a+x /usr/local/bin/order-lab
	
	#echo -e ${COLOR} sudo chown -R www-data:www-data /usr/local/bin/order-lab ${NC}
	#sudo chown -R www-data:www-data /usr/local/bin/order-lab
	#sudo chown -R nobody:nobody /usr/local/bin/order-lab 
	
	echo -e ${COLOR} sudo chown -R apache user for /usr/local/bin/order-lab ${NC}
	sudo chown -R www-data:www-data /usr/local/bin/order-lab
	
	#chown -R apache:apache /usr/local/bin/order-lab/Scanorders2/var/cache
	#chown -R apache:apache /usr/local/bin/order-lab/Scanorders2/var/logs
	
	echo ""
    sleep 1
}
     	
f_install_prepare () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Prepare ... ${NC}"
    sleep 1

	#put your custom configuration into /etc/apache2/conf.d or /etc/apache2/sites-available
	echo -e ${COLOR} Copy 000-default.conf to the server, Ubuntu:  /etc/apache2/sites-enabled ${NC}
	#cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d
	cp /usr/local/bin/order-lab/packer/000-default.conf /etc/apache2/sites-enabled

	if [ -n "$bashprotocol" ] && [ "$bashprotocol" = "https" ] && [ "$bashsslcertificate" != "installcertbot" ]
		then
			echo -e ${COLOR} HTTPS protocol=$bashprotocol, bashsslcertificate=$bashsslcertificate: Copy default-ssl.conf to the server /etc/apache2/sites-enabled ${NC}
			cp /usr/local/bin/order-lab/packer/default-ssl.conf /etc/apache2/sites-enabled
		else
			echo -e ${COLOR} HTTP protocol=$bashprotocol: Do not copy default-ssl.conf to the server ${NC}
	fi
	
	echo -e ${COLOR} Copy env ${NC}
	cp /usr/local/bin/order-lab/packer/.env /usr/local/bin/order-lab/orderflex/
	
	echo -e ${COLOR} Copy env.test ${NC}
	cp /usr/local/bin/order-lab/packer/.env.test /usr/local/bin/order-lab/orderflex/
	
	#echo @### Copy php.ini to /etc/opt/remi/php72/ ###
	#/etc/opt/remi/php72/ or /etc/
	#cp /etc/opt/remi/php72/php.ini /etc/opt/remi/php72/php_ORIG.ini
	#yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/opt/remi/php72/
	
	#echo -e ${COLOR} PHP 7.4: Copy php.ini to /etc/ ${NC}
	#cp /etc/php.ini /etc/php_ORIG.ini
	#yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/
	##Rhel7: /etc/opt/rh/rh-php56/php.ini /opt/rh/rh-php56/register.content/etc/opt/rh/rh-php56/php.ini
	##cp /etc/php.ini /etc/php_ORIG.ini
	
	#echo -e ${COLOR} PHP 8.1 Copy php.ini to /etc/opt/remi/php81/ ${NC}
	#cp /etc/php/8.1/apache2/php.ini /etc/php/8.1/apache2/php_ORIG.ini
	#yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/php/8.1/apache2/
	
	echo -e ${COLOR} Prepare: Copy php.ini to /etc/opt/remi/php82/ ${NC}
	cp /etc/php/8.2/apache2/php.ini /etc/php/8.2/apache2/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/php/8.2/apache2/
	
	echo -e ${COLOR} Copy sample.config to /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config ${NC}
	cp /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/sample.config /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config
	
	#sudo service apache2 restart
	echo -e ${COLOR} Restart apache ${NC}
	sudo systemctl restart apache2.service
	sudo systemctl status apache2.service
	#sudo journalctl -xeu apache2.service
	
	#Job for apache2.service failed because the control process exited with error code.
	#See "systemctl status apache2.service" and "journalctl -xeu apache2.service" for details.
	
	echo -e ${COLOR} Install composer ${NC}
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 
	
	#verify the data integrity of the script compare the script SHA-384 hash with the latest installer
	HASH="$(wget -q -O - https://composer.github.io/installer.sig)"	
	#Output should be "Installer verified"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"	   
	#install Composer in the /usr/local/bin directory
	#sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
	php composer-setup.php --install-dir=/usr/local/bin --filename=composer	
	
	echo -e ${COLOR} OS Info ${NC}
	sudo hostnamectl
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	echo -e ${COLOR} Check Postgresql version: psql --version ${NC}
	psql --version
	
	#echo -e ${COLOR} Check SELinux Status ${NC}
	#sestatus
	
	echo ""
    sleep 1
}	

f_install_post() {
  if [ -z "$bashemail" ] && [ "$bashsslcertificate" = "installcertbot" ] ]
      then
        #email='myemail@myemail.com'
        echo "Error: email is not provided for installcertbot option"
        echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
        exit 0
  fi
	if [ ! -z "$bashdomainname" ] && [ ! -z "$bashprotocol" ] && [ "$bashprotocol" = "https" ]
		then 
			echo -e ${COLOR} Install certbot on all OS ${NC}
			bash /usr/local/bin/order-lab/packer/install-certbot.sh "$bashdomainname" "$bashsslcertificate" "$bashemail"
		else
			echo -e ${COLOR} Domain name is not provided: Do not install certbot on all OS ${NC}
	fi	
	
	echo ""
	sleep 1
}

f_update_os
f_install_apache
f_install_postgresql15
f_install_php82
f_install_util
f_install_python3
f_install_order
f_install_prepare
#f_install_post
		   

#Log: /var/log/apache2



	  