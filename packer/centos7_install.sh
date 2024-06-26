#!/bin/bash
# CentOs installation script (Apache, PHP, Postgresql)
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
    echo -e ${COLOR} Starting update os centos 7 ... ${NC}
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

	echo -e  ${COLOR} install mod_ssl ${NC}
	sudo yum -y install mod_ssl

	#echo -e  ${COLOR} export APACHE_LOG_DIR ${NC}
	#export APACHE_LOG_DIR=/var/log/httpd

	sudo systemctl enable httpd.service
	sudo systemctl start httpd.service
	sudo systemctl status httpd.service
	
	echo ""
    sleep 1
}

f_install_postgresql15 () {
    ########## INSTALL Postgresql ##########
	#https://computingforgeeks.com/how-to-install-postgresql-on-centos-rhel-7/?expand_article=1
    echo -e "${COLOR} Installing Postgresql 15 ... ${NC}"
    sleep 1

	echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	#After repository has been added, list available repositories, update system and reboot
	echo @### List available repositories, update system ###	
	sudo yum repolist -y
	sudo yum -y update 
	#sudo systemctl reboot
	
	echo @### add EPEL repository which has dependencies required by PostgreSQL 15 ###	
	sudo yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	
	echo @### Install postgresql 15 ###	
	yum install -y postgresql15
	yum install -y postgresql15-server

	echo -e ${COLOR} Install an Ident server on Red Hat 7.x or CentOS 7.x by installing the authd and xinetd packages ${NC}
	#sudo yum install -y oidentd
	sudo yum install -y authd
	sudo yum install -y xinetd

	echo @### Optionally initialize the database postgresql-15 and enable automatic start ###	
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
	
	#Modify pg_hba.conf in /var/lib/pgsql/14/data to replace "ident" to "md5"
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

f_install_php81 () {
    ########## INSTALL APACHE 8.1 ##########
    echo "Installing apache 8.1 ..."
    sleep 1

	echo @### Install yum-utils and epel repository ###
	sudo yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	sudo yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm

	echo @### PHP1: install yum-utils -y ###
	sudo yum -y install yum-utils
	sudo yum-config-manager --disable 'remi-php*'
	sudo yum-config-manager --enable remi-php81

	echo @### PHP2: sudo yum-config-manager --enable remi-php81 ###
	sudo yum -y update

	#echo @### PHP3: Search for PHP 8.1 packages ###
	#sudo yum search php81 | more
	#sudo yum search php81 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP3: Install PHP 8.1 ###
	sudo yum -y install php81 php81-php-cli
	
	echo @### PHP4: Install PHP packages ###
	sudo yum -y install php81-php-mcrypt php81-php-gd php81-curl php81-php-ldap php81-php-zip 
	sudo yum -y install php81-php-fileinfo php81-php-opcache php81-php-fpm php81-php-mbstring php81-php-xml php81-php-json
	sudo yum -y install php81-php-pgsql php81-php-xmlreader php81-php-pdo php81-php-dom php81-php-intl
	sudo yum -y install php81-php-devel php81-php-pear php81-php-bcmath
	sudo yum -y install php81-php-common
	
	yum -y install php81-syspaths
	
	yum -y --enablerepo=remi install php81-php
	
	echo -e  ${COLOR} export PATH ${NC}
	export PATH=/opt/remi/php81/root/usr/bin:/opt/remi/php81/root/usr/sbin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
f_install_php82 () {
    ########## INSTALL APACHE 8.2 ##########
    echo "Installing apache 8.2 ..."
    sleep 1

	echo @### Install yum-utils and epel repository ###
	sudo yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	sudo yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm

	echo @### PHP1: install yum-utils -y ###
	sudo yum -y install yum-utils
	sudo yum-config-manager --disable 'remi-php*'
	sudo yum-config-manager --enable remi-php82

	echo @### PHP2: sudo yum-config-manager --enable remi-php82 ###
	sudo yum -y update

	#echo @### PHP3: Search for PHP 8.1 packages ###
	#sudo yum search php81 | more
	#sudo yum search php81 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP3: Install PHP 8.2 ###
	sudo yum -y install php82 php82-php-cli
	
	echo @### PHP4: Install PHP packages ###
	#sudo yum -y install php81-php-mcrypt php81-php-gd php81-curl php81-php-ldap php81-php-zip 
	#sudo yum -y install php81-php-fileinfo php81-php-opcache php81-php-fpm php81-php-mbstring php81-php-xml php81-php-json
	#sudo yum -y install php81-php-pgsql php81-php-xmlreader php81-php-pdo php81-php-dom php81-php-intl
	#sudo yum -y install php81-php-devel php81-php-pear php81-php-bcmath
	#sudo yum -y install php81-php-common
	sudo yum -y install php82-php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	sudo yum -y install php82-php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	
	yum -y install php82-syspaths
	
	yum -y --enablerepo=remi install php82-php
	
	echo -e  ${COLOR} export PATH ${NC}
	export PATH=/opt/remi/php82/root/usr/bin:/opt/remi/php82/root/usr/sbin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
	
	echo -e  ${COLOR} Check PHP version: php -v ${NC}
	php -v
	
	# Restart Apache
    sudo systemctl restart httpd.service
	
	echo ""
    sleep 1
}
f_install_php83 () {
    ########## INSTALL APACHE 8.3 ##########
    echo "Installing apache 8.3 ..."
    sleep 1

	echo @### Install yum-utils and epel repository ###
	#sudo yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
	sudo yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
	sudo yum -y install epel-release

	echo @### PHP1: install yum-utils -y ###
	sudo yum -y install yum-utils
	sudo yum-config-manager --disable 'remi-php*'
	sudo yum-config-manager --enable remi-php83

	echo @### PHP2: sudo yum-config-manager --enable remi-php83 ###
	sudo yum -y update

	#echo @### PHP3: Search for PHP 8.1 packages ###
	#sudo yum search php81 | more
	#sudo yum search php81 | egrep 'fpm|gd|mysql|memcache'
	
	echo @### PHP3: Install PHP 8.3 ###
	sudo yum -y install php83 php83-php-cli
	
	echo @### PHP4: Install PHP packages ###
	#sudo yum -y install php81-php-mcrypt php81-php-gd php81-curl php81-php-ldap php81-php-zip 
	#sudo yum -y install php81-php-fileinfo php81-php-opcache php81-php-fpm php81-php-mbstring php81-php-xml php81-php-json
	#sudo yum -y install php81-php-pgsql php81-php-xmlreader php81-php-pdo php81-php-dom php81-php-intl
	#sudo yum -y install php81-php-devel php81-php-pear php81-php-bcmath
	#sudo yum -y install php81-php-common
	sudo yum -y install php83-php-{cli,mcrypt,gd,curl,ldap,zip,fileinfo,opcache,fpm,mbstring,xml,json}
	sudo yum -y install php83-php-{pgsql,xmlreader,pdo,dom,intl,devel,pear,bcmath,common}
	
	yum -y install php83-syspaths
	
	yum -y --enablerepo=remi install php83-php
	
	echo -e  ${COLOR} export PATH ${NC}
	export PATH=/opt/remi/php83/root/usr/bin:/opt/remi/php83/root/usr/sbin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
	
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

	echo -e ${COLOR} Install vim ${NC}		
	sudo yum install -y vim	

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
	#old url: wget https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox-0.12.5-1.centos7.x86_64.rpm
	wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.5/wkhtmltox-0.12.5-1.centos7.x86_64.rpm
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
	
	echo -e ${COLOR} PHP Copy php.ini ${NC}
	cp /etc/php.ini /etc/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/
	
	echo -e ${COLOR} Copy sample.config to /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config ${NC}
	cp /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/sample.config /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/db.config
	
	#sudo service apache2 restart
	sudo systemctl restart httpd.service
	sudo systemctl status httpd.service
	
	echo -e ${COLOR} Install composer ${NC}
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 
	
	#verify the data integrity of the script compare the script SHA-384 hash with the latest installer
	HASH="$(wget -q -O - https://composer.github.io/installer.sig)"	
	#Output should be "Installer verified"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"	   
	#install Composer in the /usr/local/bin directory
	#sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
	php composer-setup.php --install-dir=/usr/local/bin --filename=composer	
	
	#echo -e "${COLOR} Installing env python for "
	#cd /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/
	#python3 -m venv venv
	#source venv/bin/activate
	#sudo pip3 install -r requirements.txt
	#python -m pip install -r requirements.txt
	#echo -e "${COLOR} Installing additional.sh: env for python"
	#source /usr/local/bin/order-lab/packer/additional.sh
	
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
			#bash /usr/local/bin/order-lab/packer/install-certbot-centos.sh "$bashdomainname"
			bash /usr/local/bin/order-lab/packer/install-certbot.sh "$bashdomainname" "$bashsslcertificate" "$bashemail"
		else
			echo -e ${COLOR} Domain name is not provided: Do not install certbot ${NC}
	fi	
	
	echo ""
	sleep 1
}

f_update_os
f_install_apache
f_install_postgresql15
#f_install_php81
#f_install_php82
f_install_php83
f_install_util
f_install_python3
f_install_order
f_install_prepare
#f_install_post
		   
#https://www.digitalocean.com/community/tutorials/apache-configuration-error-ah00558-could-not-reliably-determine-the-server-s-fully-qualified-domain-name
#sudo apachectl configtest		   
#sudo systemctl restart httpd.service
#sudo systemctl restart postgresql-15		   
#Centos apache log: /var/log/httpd	
#postfresql log: /var/lib/pgsql/15/data/log/   





	  