#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)

echo @### Get bash_dbuser bash_dbpass ###
#bashdbuser=$1
#bashdbpass=$2
echo bashdbuser=$bashdbuser
echo bashdbpass=$bashdbpass

#https://gitlab.com/Danny_Pham/WriteBash.com/blob/master/Install/06-Script_install_LAMP_PHP_7.2_on_CentOS_7.sh
# Function update os
f_update_os () {
    echo "Starting update os ..."
    sleep 1

    sudo yum update
    sudo yum upgrade -y

    echo ""
    sleep 1
}

# Function install LAMP stack
f_install_apache () {
    ########## INSTALL APACHE ##########
    echo "Installing apache ..."
    sleep 1

	sudo yum install httpd -y
	sudo systemctl enable httpd.service
	sudo systemctl start httpd.service
	sudo systemctl status httpd.service
	
	echo ""
    sleep 1
}

f_install_postgresql12 () {
    ########## INSTALL Postgresql ##########
    echo "Installing Postgresql ..."
    sleep 1

	echo @### Install the repository RPM, client and server packages ###		
	sudo yum install https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	yum install postgresql12 -y
	yum install postgresql12-server -y
	#sudo yum -y install postgresql11 postgresql11-server postgresql11-contrib postgresql11-libs

	#echo @### (use this???) /usr/pgsql-11/bin/postgresql-11-setup initdb ###
	echo @### Optionally initialize the database and enable automatic start ###	
	/usr/pgsql-12/bin/postgresql-12-setup initdb
	systemctl enable postgresql-12
	systemctl start postgresql-12

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	echo ""
    sleep 1
}

f_install_php72_ORIG () {
    ########## INSTALL APACHE 7.2 ##########
    echo "Installing apache 7.2 ..."
    sleep 1

	echo @### Install yum-utils and enable epel repository ###
	sudo yum -y install epel-release
	sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y

	echo @### PHP1: install yum-utils -y ###
	sudo yum install yum-utils -y

	echo @### PHP2: sudo yum-config-manager --enable remi-php72 ###
	yum-config-manager --enable remi-php72 -y

	echo @### PHP3: sudo yum install php72 -y ###
	yum install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo php-pear -y
	
	# Config to fix error Apache not load PHP file
    chown -R apache:apache /var/www
    sed -i '/<Directory \/>/,/<\/Directory/{//!d}' /etc/httpd/conf/httpd.conf
    sed -i '/<Directory \/>/a\    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted' /etc/httpd/conf/httpd.conf

	# Restart Apache
    sudo systemctl restart httpd.service

	#echo @### PHP3: sudo yum install php-common -y ###
	#sudo yum update
	#sudo yum install php-common -y

	#echo @### PHP4: sudo yum install php-cli and others -y ###
	#TODO: error: No package vailable
	#sudo yum install -y php72 php72-php-fpm php72-php-gd php72-php-json php72-php-mbstring php72-php-mysqlnd php72-php-xml php72-php-xmlrpc php72-php-opcache
	
	sudo systemctl enable php72-php-fpm.service
	sudo systemctl start php72-php-fpm.service
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
f_install_php72 () {
    ########## INSTALL APACHE 7.2 ##########
    echo "Installing apache 7.2 ..."
    sleep 1

	echo @### Install yum-utils and enable epel repository ###
	sudo yum -y install epel-release
	sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y

	echo @### PHP1: install yum-utils -y ###
	sudo yum install yum-utils -y

	echo @### PHP2: sudo yum-config-manager --enable remi-php72 ###
	sudo yum-config-manager --enable remi-php72 -y
	#created /var/lib/yum/repos/x86_64/7/remi-php72
	sudo yum update -y

	#echo @### PHP3: Search for PHP 7.2 packages ###
	#sudo yum search php72 | more
	#sudo yum search php72 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP4: Install PHP 7.2 ###
	#sudo yum -y install php72
	sudo yum -y install php php-opcache
	
	echo @### PHP4: Install PHP packages ###
	#sudo yum -y install php72-php-fpm php72-php-gd php72-php-json php72-php-mbstring php72-php-mysqlnd php72-php-xml php72-php-xmlrpc php72-php-opcache
	sudo yum -y install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo
	
	# Config to fix error Apache not load PHP file
    #chown -R apache:apache /var/www
    #sed -i '/<Directory \/>/,/<\/Directory/{//!d}' /etc/httpd/conf/httpd.conf
    #sed -i '/<Directory \/>/a\    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted' /etc/httpd/conf/httpd.conf

	# Restart Apache
    #systemctl restart httpd.service

	#echo @### PHP3: sudo yum install php-common -y ###
	#sudo yum update
	#sudo yum install php-common -y

	#echo @### PHP4: sudo yum install php-cli and others -y ###
	#TODO: error: No package vailable
	#sudo yum install -y php72 php72-php-fpm php72-php-gd php72-php-json php72-php-mbstring php72-php-mysqlnd php72-php-xml php72-php-xmlrpc php72-php-opcache
	
	sudo systemctl enable php-php-fpm.service
	sudo systemctl start php-php-fpm.service
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
f_install_php54 () {
    ########## INSTALL APACHE 5.6 ##########
    echo "Installing apache 5.6 ..."
    sleep 1

	echo @### Install yum-utils and enable epel repository ###
	sudo yum -y install epel-release
	sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y

	echo @### PHP1: install yum-utils -y ###
	sudo yum install yum-utils -y

	echo @### PHP2: sudo yum-config-manager --enable remi-php56 ###
	sudo yum-config-manager --enable remi-php56 -y
	sudo yum update -y
	
	echo @### PHP4: Install PHP 5.6 ###
	sudo yum -y install php php-opcache
	
	echo @### PHP4: Install PHP packages ###
	sudo yum -y install php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo
	
	# Config to fix error Apache not load PHP file
    #chown -R apache:apache /var/www
    #sed -i '/<Directory \/>/,/<\/Directory/{//!d}' /etc/httpd/conf/httpd.conf
    #sed -i '/<Directory \/>/a\    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted' /etc/httpd/conf/httpd.conf

	# Restart Apache
    #systemctl restart httpd.service

	#echo @### PHP3: sudo yum install php-common -y ###
	#sudo yum update
	#sudo yum install php-common -y

	#echo @### PHP4: sudo yum install php-cli and others -y ###
	#TODO: error: No package vailable
	#sudo yum install -y php72 php72-php-fpm php72-php-gd php72-php-json php72-php-mbstring php72-php-mysqlnd php72-php-xml php72-php-xmlrpc php72-php-opcache
	
	#sudo systemctl enable php-php-fpm.service
	#sudo systemctl start php-php-fpm.service
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
#https://www.svnlabs.com/blogs/install-apache-mysql-php-5-6-on-centos-7/
f_install_php56 () {
    ########## INSTALL PHP 5.6 ##########
    echo "Installing php 5.6 ..."
    sleep 1
	
	#Install EPEL repository
	sudo rpm -Uvh http://vault.centos.org/7.0.1406/extras/x86_64/Packages/epel-release-7-5.noarch.rpm
	#Install remi repository
	sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm	
	
	#Enable remi
	#echo "Enable remi"
	#yum -y --enablerepo=remi,remi-php56 install php php-common
	
	#Install php 5.6 on Centos 7
	echo "Enable remi and Install php 5.6 on Centos 7"
	sudo yum -y --enablerepo=remi,remi-php56 install php-cli php-pear php-pdo php-mysql php-mysqlnd php-pgsql php-sqlite php-gd php-mbstring php-mcrypt php-xml php-simplexml php-curl php-zip
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	#chown -R apache:apache /var/www/html/
	#chmod -R 775 /var/www/
	
	echo ""
    sleep 1
}

f_install_util () {
    ########## INSTALL UTILITIES ##########
    echo "Installing util ..."
    sleep 1

	echo @### Install Git ###		
	sudo yum install -y git	
	
	echo @### Install wkhtmltopdf, libreoffice, ghostscript, pdftk ###
	#sudo yum update
	sudo yum install -y xvfb libfontconfig wkhtmltopdf	
	sudo yum install -y libreoffice	
	sudo yum install -y ghostscript
	sudo yum install -y pdftk  

	sudo yum install -y wget unzip
	
	echo ""
    sleep 1
}


f_install_order () {
    ########## Clone ORDER ##########
    echo "Installing order ..."
    sleep 1
	
	echo @### Install Git ###		
	sudo yum install -y git	

	echo @### Clone ORDER and copy config and php.ini files, install composer ###
	ssh-keyscan github.com >> ~/.ssh/known_hosts
	cd /usr/local/bin/
	git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
	
	#chown -R apache:apache /var/www
	echo @### sudo chmod a+x /usr/local/bin/order-lab ###
	sudo chmod a+x /usr/local/bin/order-lab
	echo @### sudo chown -R www-data:www-data /usr/local/bin/order-lab ###
	sudo chown -R www-data:www-data /usr/local/bin/order-lab
	
	echo ""
    sleep 1
}
     	
f_install_prepare () {
    ########## Clone ORDER ##########
    echo "Prepare ..."
    sleep 1

	echo @### Copy 000-default.conf to /etc/httpd/conf.d ###
	cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d
	
	#echo @### Copy php.ini to /etc/opt/remi/php72/ ###
	#/etc/opt/remi/php72/ or /etc/
	#cp /etc/opt/remi/php72/php.ini /etc/opt/remi/php72/php_ORIG.ini
	#yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/opt/remi/php72/
	
	echo @### Copy php.ini to /etc/ ###
	cp /etc/php.ini /etc/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/
	
	#sudo service apache2 restart
	sudo systemctl restart httpd.service
	
	echo @### Install composer ###
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 
	
	#verify the data integrity of the script compare the script SHA-384 hash with the latest installer
	HASH="$(wget -q -O - https://composer.github.io/installer.sig)"	
	#Output should be "Installer verified"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"	   
	#install Composer in the /usr/local/bin directory
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer		
	
	echo ""
    sleep 1
}	

f_update_os
f_install_apache
f_install_postgresql12
#f_install_php72
f_install_php56
f_install_util
f_install_order
f_install_prepare
		   

	  