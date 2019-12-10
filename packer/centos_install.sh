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

#https://gitlab.com/Danny_Pham/WriteBash.com/blob/master/Install/06-Script_install_LAMP_PHP_7.2_on_CentOS_7.sh
# Function update os
f_update_os () {
    echo -e ${COLOR} Starting update os centos ... ${NC}
	#echo -e ${COLOR} @### Test Color 1 ### ${NC}	
	#echo -e "${COLOR}" @### Test Color 2 ### "${NC}"	
	#exit 0
    sleep 1

	echo -e ${COLOR} sudo yum update -y ${NC}
    sudo yum update -y
	
	echo -e ${COLOR} sudo yum upgrade -y ${NC}
    sudo yum upgrade -y
	
	echo -e ${COLOR} Disable SELinux ${NC}
	#Set "sudo setenforce 0" for now to complete composer later
	sudo setenforce 0
	sed -i -e "s/SELINUX=enforcing/SELINUX=disabled/g" /etc/selinux/config
	
	echo -e ${COLOR} Check SELinux Status ${NC}
	sestatus

    echo ""
    sleep 1
}

# Function install LAMP stack
f_install_apache () {
    ########## INSTALL APACHE ##########
    echo -e "${COLOR} Installing apache ... ${NC}"
    sleep 1

	sudo yum install httpd -y
	sudo systemctl enable httpd.service
	sudo systemctl start httpd.service
	sudo systemctl status httpd.service
	
	echo ""
    sleep 1
}

f_install_postgresql12 () {
	#This causes error: Undefined column: 7 ERROR: column min_value does not exists (FROM "scan_accession_id_seq")
    ########## INSTALL Postgresql ##########
    echo -e "${COLOR} Installing Postgresql 12 ... ${NC}"
    sleep 1

	echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	yum install -y postgresql12
	yum install -y postgresql12-server
	#sudo yum -y install postgresql11 postgresql11-server postgresql11-contrib postgresql11-libs

	sudo yum -y install oidentd

	#echo @### (use this???) /usr/pgsql-11/bin/postgresql-11-setup initdb ###
	echo @### Optionally initialize the database and enable automatic start ###	
	sudo /usr/pgsql-12/bin/postgresql-12-setup initdb
	sudo systemctl enable postgresql-12
	sudo systemctl start postgresql-12

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	#Modify pg_hba.conf in /var/lib/pgsql/12/data to replace "ident" to "md5"
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/12/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /var/lib/pgsql/12/data/pg_hba.conf
	
	echo -e ${COLOR} Modify pg_hba.conf ident to md5 ${NC}
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/12/data/pg_hba.conf
	
	#echo -e ${COLOR} Add TEXTTOEND to pg_hba.conf ${NC}
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/12/data/pg_hba.conf
	
	#echo -e ${COLOR} Replace TEXTTOEND in pg_hba.conf ${NC}
	sed -i "s/TEXTTOEND/host all all 0.0.0.0\/0 md5/g" /var/lib/pgsql/12/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/12/data/postgresql.conf
	
	echo -e ${COLOR} Set port ${NC}
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/12/data/postgresql.conf
		
	sudo systemctl restart postgresql-12
	
	echo ""
    sleep 1
}

#https://www.digitalocean.com/community/tutorials/how-to-install-and-use-postgresql-on-centos-7
f_install_postgresql_9.2_12 () {
	echo -e "${COLOR} Installing Postgresql (9.2?) ... ${NC}"
    sleep 1

	#echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	#sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	sudo yum install -y postgresql-server postgresql-contrib
	
	sudo yum install -y oidentd
	
	echo -e ${COLOR} Optionally initialize the database and enable automatic start ${NC}
	sudo postgresql-setup initdb
	
	echo -e ${COLOR} Start and enable postgresql ${NC}
	sudo systemctl start postgresql
	sudo systemctl enable postgresql
	sudo systemctl status postgresql
	
	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	#sudo -Hiu postgres psql -c "CREATE USER symfony WITH PASSWORD 'symfony'"
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/12/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/12/data to replace "ident" to "md5"
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/12/data/pg_hba.conf
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/12/data/pg_hba.conf
	sed -i -e "s/TEXTTOEND/host all all 0.0.0.0/0 md5/g" /var/lib/pgsql/12/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses on specified port ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/12/data/postgresql.conf
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/12/data/postgresql.conf
	
	sudo systemctl restart postgresql-12
	
	echo -e ${COLOR} Check Postgresql version: psql --version ${NC}
	psql --version
	
	echo ""
    sleep 1
}
#https://www.linode.com/docs/databases/postgresql/how-to-install-postgresql-relational-databases-on-centos-7/
f_install_postgresql () {
	echo -e "${COLOR} Installing Postgresql (9.2.24) ... ${NC}"
    sleep 1

	#echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	#sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	sudo yum install -y postgresql-server postgresql-contrib
	
	echo -e ${COLOR} Optionally initialize the database and enable automatic start ${NC}
	sudo postgresql-setup initdb
	
	echo -e ${COLOR} Start and enable postgresql ${NC}
	sudo systemctl start postgresql
	sudo systemctl enable postgresql
	sudo systemctl status postgresql
	
	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	#sudo -Hiu postgres psql -c "CREATE USER symfony WITH PASSWORD 'symfony'"
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /var/lib/pgsql/data/pg_hba.conf
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/data/pg_hba.conf
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/data/pg_hba.conf
	sed -i -e "s/TEXTTOEND/host all all 0.0.0.0/0 md5/g" /var/lib/pgsql/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses on specified port ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/data/postgresql.conf
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/data/postgresql.conf
	
	sudo systemctl restart postgresql
	
	echo -e ${COLOR} Check Postgresql version: psql --version ${NC}
	psql --version
	
	echo ""
    sleep 1
}

#https://www.svnlabs.com/blogs/install-apache-mysql-php-5-6-on-centos-7/
f_install_php56 () {
    ########## INSTALL PHP 5.6 ##########
    echo -e "${COLOR} Installing php 5.6 ... ${NC}"
    sleep 1
	
	#Install EPEL repository
	#sudo rpm -Uvh http://vault.centos.org/7.0.1406/extras/x86_64/Packages/epel-release-7-5.noarch.rpm
	#Install remi repository
	#sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm	
	
	#https://www.tecmint.com/install-php-5-6-on-centos-7/
	echo -e ${COLOR} Install: noarch and remi ${NC}	
	sudo yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	sudo yum install -y http://rpms.remirepo.net/enterprise/remi-release-7.rpm
	
	echo -e ${COLOR} Install: yum install -y yum-utils ${NC}		
	sudo yum install -y yum-utils
	
	#Enable remi
	#echo "Enable remi"
	#yum -y --enablerepo=remi,remi-php56 install php php-common
	
	echo -e  ${COLOR} Enable remi-php56 ${NC}
	yum-config-manager --enable remi-php56
	
	echo -e ${COLOR} Install php 5.6: install -y php ${NC}	
	sudo yum install -y php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo php-pear php-pdo php-pgsql php-xml php-simplexml php-zip php-mbstring php-intl
	
	#echo -e ${COLOR} Install php 5.6: install -y php ${NC}	
	#sudo yum install -y php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap 

	#echo -e ${COLOR} Install php extensions ${NC}	
	#sudo yum install -y php-zip php-fileinfo php-pear php-pdo php-pgsql php-xml php-simplexml php-zip php-mbstring

	#Install php 5.6 on Centos 7
	#echo "Enable remi and Install php 5.6 on Centos 7"
	#sudo yum -y --enablerepo=remi,remi-php56 install php-cli php-pear php-pdo php-mysql php-mysqlnd php-pgsql php-sqlite php-gd php-mbstring php-mcrypt php-xml php-simplexml php-curl php-zip
	
	#Install php 5.6 on Rhel 7
	#https://docs.nextcloud.com/server/13.0.0/admin_manual/installation/php_56_installation.html
	#1) subscription-manager repos --enable rhel-server-rhscl-7-eus-rpms
	#2) yum install rh-php56 rh-php56-php rh-php56-php-gd rh-php56-php-mbstring
	#3) sudo yum install rh-php56-php-pgsql
	#4) cp /opt/rh/httpd24/root/etc/httpd/conf.d/rh-php56-php.conf /etc/httpd/conf.d/
	#   cp /opt/rh/httpd24/root/etc/httpd/conf.modules.d/10-rh-php56-php.conf /etc/httpd/conf.modules.d/
	#   cp /opt/rh/httpd24/root/etc/httpd/modules/librh-php56-php5.so /etc/httpd/modules/
	#yum install php56-php-cli
	
	#/opt/rh/rh-php56/root/usr/bin/php -v
	#ln -s /usr/bin/php56 /usr/bin/php
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	#chown -R apache:apache /var/www/html/
	#chmod -R 775 /var/www/

	echo -e  ${COLOR} php -v ${NC}
	php -v
	
	echo ""
    sleep 1
}

f_install_util () {
    ########## INSTALL UTILITIES ##########
    echo "Installing util ..."
    sleep 1

	echo -e ${COLOR} Install Git ${NC}		
	sudo yum install -y git	
	
	echo -e ${COLOR} Install libreoffice, ghostscript, pdftk ${NC}
	#sudo yum update
	sudo yum install -y xvfb libfontconfig	
	sudo yum install -y libreoffice	
	sudo yum install -y ghostscript
	#sudo yum install -y pdftk  

	sudo yum install -y wget unzip
	
	#https://gist.github.com/apphancer/8654e82aa582d1cf02c955536df06449
	#https://jaimegris.wordpress.com/2015/03/04/how-to-install-wkhtmltopdf-in-centos-7-0/
	echo -e ${COLOR} Install wkhtmltopdf dependencies xorg-x11-fonts-75dpi and xorg-x11-fonts-Type1 ${NC}
	yum install -y xorg-x11-fonts-75dpi
	yum install -y xorg-x11-fonts-Type1
	yum install xz
	echo -e ${COLOR} Install wkhtmltopdf ${NC}
	wget https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox-0.12.5-1.centos7.x86_64.rpm
	sudo rpm -Uvh wkhtmltox-0.12.5-1.centos7.x86_64.rpm
	
	echo -e ${COLOR} Install xorg-x11-server-Xvfb ${NC}
	sudo yum install -y xorg-x11-server-Xvfb
	
	echo -e ${COLOR} Get version wkhtmltopdf ${NC}
	/usr/bin/xvfb-run wkhtmltopdf --version
	
	#http://bashworkz.com/installing-pdftk-on-centos-5-and-6-pdf-management-utility-tool/
	#echo -e ${COLOR} Install pdftk ${NC}
	#sudo yum install -y libgcj
	#yum install -y https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/pdftk-2.02-1.el6.x86_64.rpm
	#pdftk --help | more
	
	#https://github.com/documentcloud/docsplit/issues/123
	echo -e ${COLOR} Install pdftk ${NC}
	sudo wget https://copr.fedorainfracloud.org/coprs/robert/gcj/repo/epel-7/robert-gcj-epel-7.repo -P /etc/yum.repos.d
	sudo wget https://copr.fedorainfracloud.org/coprs/robert/pdftk/repo/epel-7/robert-pdftk-epel-7.repo -P /etc/yum.repos.d
	sudo yum install -y pdftk
	
	#http://www.vassox.com/linux-general/installing-phantomjs-on-centos-7-rhel/
	echo -e ${COLOR} Install PhantomJS ${NC}
	sudo yum install -y dnf
	sudo dnf install -y glibc fontconfig
	sudo yum install -y lbzip2
	sudo yum install -y fontconfig
	sudo yum install -y freetype
	sudo yum install -y wget
	sudo yum install -y bzip2
	cd /opt
	sudo wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2
	sudo tar -xvf phantomjs-2.1.1-linux-x86_64.tar.bz2
	ln -s /opt/phantomjs-2.1.1-linux-x86_64/bin/phantomjs /usr/local/bin/phantomjs phantomjs --version
	
	echo ""
    sleep 1
}


f_install_order () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Installing order ..."
    sleep 1
	
	echo @### Install Git ###		
	sudo yum install -y git	

	echo -e ${COLOR} Clone ORDER and copy config and php.ini files, install composer ${NC}
	ssh-keyscan github.com >> ~/.ssh/known_hosts
	cd /usr/local/bin/
	git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
	
	#chown -R apache:apache /var/www
	echo -e ${COLOR} sudo chmod a+x /usr/local/bin/order-lab ${NC}
	sudo chmod a+x /usr/local/bin/order-lab
	
	#echo -e ${COLOR} sudo chown -R www-data:www-data /usr/local/bin/order-lab ${NC}
	#sudo chown -R www-data:www-data /usr/local/bin/order-lab
	#sudo chown -R nobody:nobody /usr/local/bin/order-lab 
	
	echo -e ${COLOR} sudo chown -R apache:apache /usr/local/bin/order-lab ${NC}
	sudo chown -R apache:apache /usr/local/bin/order-lab
	
	#chown -R apache:apache /usr/local/bin/order-lab/Scanorders2/var/cache
	#chown -R apache:apache /usr/local/bin/order-lab/Scanorders2/var/logs
	
	echo ""
    sleep 1
}
     	
f_install_prepare () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Prepare ... ${NC}"
    sleep 1

	echo -e ${COLOR} Copy 000-default.conf to /etc/httpd/conf.d ${NC}
	cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d
	
	#echo @### Copy php.ini to /etc/opt/remi/php72/ ###
	#/etc/opt/remi/php72/ or /etc/
	#cp /etc/opt/remi/php72/php.ini /etc/opt/remi/php72/php_ORIG.ini
	#yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/opt/remi/php72/
	
	echo -e ${COLOR} Copy php.ini to /etc/ ${NC}
	cp /etc/php.ini /etc/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/
	#Rhel7: /etc/opt/rh/rh-php56/php.ini /opt/rh/rh-php56/register.content/etc/opt/rh/rh-php56/php.ini
	#cp /etc/php.ini /etc/php_ORIG.ini
	
	#sudo service apache2 restart
	sudo systemctl restart httpd.service
	
	echo -e ${COLOR} Install composer ${NC}
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 
	
	#verify the data integrity of the script compare the script SHA-384 hash with the latest installer
	HASH="$(wget -q -O - https://composer.github.io/installer.sig)"	
	#Output should be "Installer verified"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"	   
	#install Composer in the /usr/local/bin directory
	sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer		
	
	echo -e ${COLOR} OS Info ${NC}
	sudo hostnamectl
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	echo -e ${COLOR} Check Postgresql version: psql --version ${NC}
	psql --version
	
	echo -e ${COLOR} Check SELinux Status ${NC}
	sestatus
	
	echo ""
    sleep 1
}	

f_update_os
f_install_apache
f_install_postgresql12
#f_install_postgresql
#f_install_php72
f_install_php56
f_install_util
f_install_order
f_install_prepare
		   




f_install_pgloader () {
	echo -e ${COLOR} Install pgloader ${NC}
	#yum install -y pgloader
	sudo mkdir /usr/local/bin/temp
	sudo chown -R apache:apache /usr/local/bin/temp
	cd /usr/local/bin/temp
	git clone https://github.com/dimitri/pgloader.git
	cd pgloader
	chmod +x ./bootstrap-centos7.sh
	sudo ./bootstrap-centos7.sh
	make pgloader
	cd /usr/local/bin/
}


f_install_php72_ORIG () {
    ########## INSTALL APACHE 7.2 ##########
    echo "Installing apache 7.2 ..."
    sleep 1

	echo @### Install yum-utils and enable epel repository ###
	sudo yum -y install epel-release - 
	sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y

	echo @### PHP1: install yum-utils -y ###
	sudo yum install yum-utils -y

	echo @### PHP2: sudo yum-config-manager --enable remi-php72 ###
	yum-config-manager --enable remi-php72 -y

	echo @### PHP3: sudo yum install php72 -y ###
	yum install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo php-pear -y
	
	# Config to fix error Apache not load PHP file
    chown -R apache:apache /var/www
    #sed -i '/<Directory \/>/,/<\/Directory/{//!d}' /etc/httpd/conf/httpd.conf
    #sed -i '/<Directory \/>/a\    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted' /etc/httpd/conf/httpd.conf

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
    ########## INSTALL APACHE 5.4 ##########
    echo -e "${COLOR} Installing apache 5.4 ... ${NC}"
    sleep 1

	echo @### Install yum-utils and enable epel repository ###
	sudo yum -y install epel-release
	sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y

	echo @### PHP1: install yum-utils -y ###
	sudo yum install yum-utils -y

	echo @### PHP2: sudo yum-config-manager --enable remi-php56 ###
	sudo yum-config-manager --enable remi-php56 -y
	sudo yum update -y
	
	echo @### PHP4: Install PHP 5.4 ###
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


	  