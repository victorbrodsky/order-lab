#!/bin/bash
# alma8 installation script (alma8, PHP, Postgresql)
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
    echo -e ${COLOR} Starting update os alma8 ... ${NC}
	#echo -e ${COLOR} @### Test Color 1 ### ${NC}	
	#echo -e "${COLOR}" @### Test Color 2 ### "${NC}"	
	#exit 0
    sleep 1

	echo -e ${COLOR} sudo dnf check-update ${NC}
	sudo dnf check-update
	
	echo -e ${COLOR} check-update command again to ensure the system is up-to-date ${NC}
	sudo dnf update -y

	echo -e ${COLOR} sudo dnf update -y ${NC}
    sudo dnf update -y
	
	#echo -e ${COLOR} Once the system is updated, reboot the system ${NC}
    #sudo reboot
	
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
# https://linux.how2shout.com/how-to-install-apache-on-almalinux-8-rocky-linux-8/
f_install_apache () {
    ########## INSTALL APACHE ##########
    echo -e "${COLOR} Installing apache ... ${NC}"
    sleep 1

	sudo dnf install httpd -y
	
	echo -e  ${COLOR} install mod_ssl ${NC}
	sudo dnf -y install mod_ssl
	
	sudo systemctl enable httpd.service
	sudo systemctl start httpd.service
	sudo systemctl status httpd.service
	
	echo -e "${COLOR} Allow port 80 in Firewall on AlmaLinux/Rocky 8 ${NC}"
	sudo firewall-cmd --zone=public --permanent --add-port=80/tcp
	sudo firewall-cmd --zone=public --permanent --add-port=443/tcp
	sudo firewall-cmd --reload
	
	echo ""
    sleep 1
}

f_install_postgresql15 () {
	#https://computingforgeeks.com/install-postgresql-server-on-rocky-almalinux/?expand_article=1
    ########## INSTALL Postgresql ##########
    echo -e "${COLOR} Installing Postgresql 15 ... ${NC}"
    sleep 1

	echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	#sudo dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	sudo dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-8-x86_64/pgdg-redhat-repo-latest.noarch.rpm
	
	echo -e ${COLOR} disable the built-in PostgreSQL module ${NC}
	sudo dnf -qy module disable postgresql
	
	#After repository has been added, list available repositories, update system and reboot
	#echo @### List available repositories, update system ###	
	#sudo dnf repolist -y
	#sudo dnf -y update 
	#sudo systemctl reboot
	
	echo @### Install postgresql 15 ###	
	sudo dnf install -y postgresql15-server

	#echo -e ${COLOR} Install an Ident server on Red Hat 7.x or CentOS 7.x by installing the authd and xinetd packages ${NC}
	#sudo yum install -y oidentd
	#sudo dnf install -y authd
	#sudo dnf install -y xinetd

	echo @### Optionally initialize the database and enable automatic start ###	
	sudo /usr/pgsql-15/bin/postgresql-15-setup initdb
	sudo systemctl enable postgresql-15
	sudo systemctl start postgresql-15

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	#echo @### Create system DB and create user $bashdbuser with password $bashdbpass###
	#sudo -Hiu postgres createdb systemdb
	#sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE systemdb to $bashdbuser"
	
	#Modify pg_hba.conf in /var/lib/pgsql/15/data to replace "ident" to "md5"
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/15/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /var/lib/pgsql/15/data/pg_hba.conf
	
	echo -e ${COLOR} Modify pg_hba.conf ident to md5 ${NC}
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/15/data/pg_hba.conf
	
	#echo -e ${COLOR} Add TEXTTOEND to pg_hba.conf ${NC}
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/15/data/pg_hba.conf
	
	#echo -e ${COLOR} Replace TEXTTOEND in pg_hba.conf ${NC}
	sed -i "s/TEXTTOEND/host all all 0.0.0.0\/0 md5/g" /var/lib/pgsql/15/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/15/data/postgresql.conf
	
	echo -e ${COLOR} Set port ${NC}
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/15/data/postgresql.conf
		
	sudo systemctl restart postgresql-15
	
	echo ""
    sleep 1
}
f_install_postgresql16 () {
	#https://computingforgeeks.com/install-postgresql-server-on-rocky-almalinux/?expand_article=1
    ########## INSTALL Postgresql ##########
    echo -e "${COLOR} Installing Postgresql 16 ... ${NC}"
    sleep 1

	echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	#sudo dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	sudo dnf install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-8-x86_64/pgdg-redhat-repo-latest.noarch.rpm
	
	echo -e ${COLOR} disable the built-in PostgreSQL module ${NC}
	sudo dnf -qy module disable postgresql
	
	#After repository has been added, list available repositories, update system and reboot
	#echo @### List available repositories, update system ###	
	#sudo dnf repolist -y
	#sudo dnf -y update 
	#sudo systemctl reboot
	
	echo @### Install postgresql 16 ###	
	sudo dnf install -y postgresql16-server postgresql16

	#echo -e ${COLOR} Install an Ident server on Red Hat 7.x or CentOS 7.x by installing the authd and xinetd packages ${NC}
	#sudo yum install -y oidentd
	#sudo dnf install -y authd
	#sudo dnf install -y xinetd

	echo @### Optionally initialize the database postgresql-16 and enable automatic start ###	
	sudo /usr/pgsql-16/bin/postgresql-16-setup initdb
	sudo systemctl enable postgresql-16
	sudo systemctl start postgresql-16

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	#echo @### Create system DB and create user $bashdbuser with password $bashdbpass###
	#sudo -Hiu postgres createdb systemdb
	#sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE systemdb to $bashdbuser"
	
	#Modify pg_hba.conf in /var/lib/pgsql/16/data to replace "ident" to "md5"
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/16/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /var/lib/pgsql/16/data/pg_hba.conf
	
	echo -e ${COLOR} Modify pg_hba.conf ident to md5 ${NC}
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/16/data/pg_hba.conf
	
	#echo -e ${COLOR} Add TEXTTOEND to pg_hba.conf ${NC}
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/16/data/pg_hba.conf
	
	#echo -e ${COLOR} Replace TEXTTOEND in pg_hba.conf ${NC}
	sed -i "s/TEXTTOEND/host all all 0.0.0.0\/0 md5/g" /var/lib/pgsql/16/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/16/data/postgresql.conf
	
	echo -e ${COLOR} Set port ${NC}
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/16/data/postgresql.conf
		
	sudo systemctl restart postgresql-16
	
	echo ""
    sleep 1
}

f_install_php82 () {
    ########## INSTALL PHP 8.2 ##########
	#https://www.linkedin.com/pulse/how-install-php-82-alma-linux-8-spin-servers/
    echo "Installing PHP 8.2 ..."
    sleep 1

	echo @### Install yum-utils and epel repository ###
	sudo dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
	sudo dnf -y install http://rpms.remirepo.net/enterprise/remi-release-8.rpm

	echo @### PHP: update DNF cache ###
	sudo dnf makecache -y

	echo @### PHP: List the repositories to ensure they are installed using the command below ###
	sudo dnf repolist

	echo @### PHP: reset the default PHP module ###
	sudo dnf module reset php -y
	
	echo @### PHP: Enable the PHP 8.2 REMI module ###
	sudo dnf module install php:remi-8.2 -y
	
	echo @### PHP: Install PHP 8.2 ###
	sudo dnf -y install php 

	#echo @### PHP3: Install PHP 8.1 ###
	#sudo yum -y install php82 php82-php-cli
	
	echo @### PHP: list of all the installable PHP modules ###
	php -m
	
	echo @### PHP: Install PHP modules ###
	sudo dnf -y install php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	sudo dnf -y install php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	
	#dnf -y install php82-syspaths
	#dnf -y --enablerepo=remi install php82-php
	
	#echo -e  ${COLOR} export PATH ${NC}
	#export PATH=/opt/remi/php82/root/usr/bin:/opt/remi/php82/root/usr/sbin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
f_install_php83 () {
    ########## INSTALL PHP 8.3 ##########
	#https://www.linkedin.com/pulse/how-install-php-82-alma-linux-8-spin-servers/
    echo "Installing PHP 8.3 ..."
    sleep 1

	echo @### Install yum-utils and epel repository ###
	sudo dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
	sudo dnf -y install http://rpms.remirepo.net/enterprise/remi-release-8.rpm

	echo @### PHP: update DNF cache ###
	sudo dnf makecache -y

	echo @### PHP: List the repositories to ensure they are installed using the command below ###
	sudo dnf repolist

	echo @### PHP: reset the default PHP module ###
	sudo dnf module reset php -y
	
	echo @### PHP: Enable the PHP 8.3 REMI module ###
	sudo dnf module install php:remi-8.3 -y
	
	echo @### PHP: Install PHP 8.3 ###
	sudo dnf -y install php 
	
	echo @### PHP: list of all the installable PHP modules ###
	php -m
	
	echo @### PHP: Install PHP modules ###
	sudo dnf -y install php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	sudo dnf -y install php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	
	echo @### PHP: Start php-fpm ###
	#sudo service php-fpm start
	#sudo service php-fpm status
	sudo systemctl enable php-fpm
	sudo systemctl start php-fpm
	sudo systemctl status php-fpm
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
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
	#disable for testing: sudo yum install -y xvfb libfontconfig	
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
	
	echo -e ${COLOR} synchronize the rpm & yumdb databases ${NC}
	yum history sync
	
	echo -e ${COLOR} Install wkhtmltopdf ${NC}
	#https://computingforgeeks.com/install-wkhtmltopdf-wkhtmltoimage-on-rocky-almalinux/?expand_article=1
	wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.centos8.x86_64.rpm
	sudo dnf install -y ./wkhtmltox-0.12.6-1.centos8.x86_64.rpm
	
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
	
	#Install pdftotext: https://github.com/spatie/pdf-to-text
	sudo yum install -y poppler-utils
	
	#Expected version ">=14". Got "12.22.12"
	echo -e ${COLOR} Install Yarn ${NC}
	curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo
	curl --silent --location https://rpm.nodesource.com/setup_16.x | sudo bash -
	sudo yum install -y yarn
	yarn --version
	
	echo -e ${COLOR} Append lsl alias to ~/.bashrc ${NC}
	echo 'alias lsl="ls -lrt"' >> ~/.bashrc
	source ~/.bashrc
	
	echo ""
    sleep 1
}

f_install_python3 () {
    ########## INSTALL PYTHON for server health monitor ##########
	echo -e "${COLOR} Installing python3 ..."
	sudo yum install -y python3
	sudo yum install -y python3-pip
	sudo pip3 install requests
	python3 -V
}

f_install_order () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Installing order ..."
    sleep 1
	
	echo -e ${COLOR} Install vim ${NC}		
	sudo yum install -y vim	
	
	echo @### Install Git ###		
	sudo yum install -y git	

	echo -e ${COLOR} Clone ORDER and copy config and php.ini files, install composer ${NC}
	ssh-keyscan github.com >> ~/.ssh/known_hosts
	cd /usr/local/bin/
	git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
	
	echo -e ${COLOR} List ORDER folder after clone ${NC}
	ls /usr/local/bin/order-lab
	
	#chown -R apache:apache /var/www
	echo -e ${COLOR} sudo chmod a+x /usr/local/bin/order-lab ${NC}
	sudo chmod a+x /usr/local/bin/order-lab
	
	echo -e ${COLOR} sudo chown -R apache:apache /usr/local/bin/order-lab ${NC}
	sudo chown -R apache:apache /usr/local/bin/order-lab
	
	echo -e ${COLOR} Fixing detected dubious ownership in repository ${NC}
	git config --global --add safe.directory /usr/local/bin/order-lab
	
	echo ""
    sleep 1
}
     	
f_install_prepare () {
    ########## Clone ORDER ##########
    echo -e "${COLOR} Prepare ... ${NC}"
    sleep 1

	echo -e ${COLOR} Copy 000-default.conf to /etc/httpd/conf.d ${NC}
	cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d

	if [ ! -z "$bashprotocol" ] && [ "$bashprotocol" = "https" ] && [ "$bashsslcertificate" != "installcertbot" ]
		then
			echo -e ${COLOR} HTTPS protocol=$bashprotocol, bashsslcertificate=$bashsslcertificate: Copy default-ssl.conf to /etc/httpd/conf.d ${NC}
			cp /usr/local/bin/order-lab/packer/default-ssl.conf /etc/httpd/conf.d
		else
			echo -e ${COLOR} HTTP protocol=$bashprotocol: Do not copy default-ssl.conf to /etc/httpd/conf.d ${NC}
	fi
	
	echo -e ${COLOR} Copy env ${NC}
	cp /usr/local/bin/order-lab/packer/.env /usr/local/bin/order-lab/orderflex/
	
	echo -e ${COLOR} Copy env.test ${NC}
	cp /usr/local/bin/order-lab/packer/.env.test /usr/local/bin/order-lab/orderflex/
	
	echo -e ${COLOR} PHP 8.2 Copy php.ini to /etc/ ${NC}
	cp /etc/php.ini /etc/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/
	
	echo -e ${COLOR} Copy sample.config to /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config ${NC}
	cp /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/sample.config /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config
	
	#sudo service apache2 restart
	sudo systemctl restart httpd.service
	
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
	
	echo -e ${COLOR} Check SELinux Status ${NC}
	sestatus
	
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
			echo -e ${COLOR} Install certbot ${NC}
			bash /usr/local/bin/order-lab/packer/install-certbot.sh "$bashdomainname" "$bashsslcertificate" "$bashemail"
		else
			echo -e ${COLOR} Domain name is not provided: Do not install certbot ${NC}
	fi	
	
	echo ""
	sleep 1
}

f_update_os
f_install_apache
#f_install_postgresql15
f_install_postgresql16
#f_install_php82
f_install_php83
f_install_util
f_install_python3
f_install_order
f_install_prepare
#f_install_post
		   





	  