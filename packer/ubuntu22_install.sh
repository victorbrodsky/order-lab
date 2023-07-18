#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)
echo @### Get bash_dbuser bash_dbpass ###
#bashdbuser=$1
#bashdbpass=$2
if [ -z "$bashdbuser" ]
  then 	
    bashdbuser=$1
fi
if [ -z "$bashdbpass" ]
  then 	
    bashdbpass=$2
fi
echo bashdbuser=$bashdbuser
echo bashdbpass=$bashdbpass

#WHITE='\033[1;37m'
COLOR='\033[1;36m'
NC='\033[0m' # No Color

# Function update os
f_update_os () {
    echo -e ${COLOR} Starting update os ubuntu 22 ... ${NC}
	
	echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
	
	sleep 1

	echo -e ${COLOR} Ubuntu 22 update ${NC}
    sudo apt update
	sudo apt -y full-upgrade
	
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
	
	echo -e ${COLOR} List the ufw application profiles ${NC}
	sudo ufw app list
	
	echo -e ${COLOR} List the ufw application profiles ${NC}
	sudo ufw allow 'Apache'
	sudo ufw status
	
	echo -e ${COLOR} Install mod_rewrite module ${NC}
	sudo a2enmod rewrite
	
	echo -e ${COLOR} Make sure the service is active ${NC}
	sudo systemctl restart apache2
	sudo systemctl status apache2
	
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
	sudo systemctl restart apache2
	
	echo ""
    sleep 1
}

f_install_php82 () {
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
	sudo systemctl restart apache2
	
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
	
	#Expected version ">=14". Got "12.22.12"
	echo -e ${COLOR} Install Yarn ${NC}
	curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo
	curl --silent --location https://rpm.nodesource.com/setup_16.x | sudo bash -
	sudo apt install -y yarn
	yarn --version
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
	# There are 000-default.conf and default-ssl.conf in /etc/apache2/sites-available

	echo -e ${COLOR} Copy 000-default.conf to the server ${NC}
	#cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d
	yes | cp /usr/local/bin/order-lab/packer/000-default.conf /etc/apache2/sites-available
	
	echo -e ${COLOR} Copy default-ssl.conf to the server ${NC}
	#cp /usr/local/bin/order-lab/packer/default-ssl.conf /etc/httpd/conf.d
	yes | cp /usr/local/bin/order-lab/packer/default-ssl.conf /etc/apache2/sites-available
	
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
	
	echo -e ${COLOR} PHP 8.1 Copy php.ini to /etc/opt/remi/php81/ ${NC}
	cp /etc/php/8.1/apache2/php.ini /etc/php/8.1/apache2/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/php/8.1/apache2/
	
	#sudo service apache2 restart
	sudo systemctl restart apache2
	sudo systemctl status apache2
	
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

f_update_os
f_install_apache
f_install_postgresql15
f_install_php82
f_install_util
f_install_python3
f_install_order
f_install_prepare
		   





	  