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

f_install_postgresql14 () {
    ########## INSTALL Postgresql ##########
    echo -e "${COLOR} Installing Postgresql 14 ... ${NC}"
    sleep 1

	echo -e ${COLOR} Install the repository RPM, client and server packages ${NC}		
	sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
	
	#After repository has been added, list available repositories, update system and reboot
	echo @### List available repositories, update system ###	
	sudo yum repolist -y
	sudo yum -y update 
	#sudo systemctl reboot
	
	echo @### Install postgresql 14 ###	
	yum install -y postgresql14
	yum install -y postgresql14-server

	echo -e ${COLOR} Install an Ident server on Red Hat 7.x or CentOS 7.x by installing the authd and xinetd packages ${NC}
	#sudo yum install -y oidentd
	sudo yum install -y authd
	sudo yum install -y xinetd

	echo @### Optionally initialize the database and enable automatic start ###	
	sudo /usr/pgsql-14/bin/postgresql-14-setup initdb
	sudo systemctl enable postgresql-14
	sudo systemctl start postgresql-14

	echo @### Create DB and create user $bashdbuser with password $bashdbpass###
	sudo -Hiu postgres createdb scanorder
	sudo -Hiu postgres psql -c "CREATE USER $bashdbuser WITH PASSWORD '$bashdbpass'"
	sudo -Hiu postgres psql -c "ALTER USER $bashdbuser WITH SUPERUSER"
	sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to $bashdbuser"
	
	#Modify pg_hba.conf in /var/lib/pgsql/14/data to replace "ident" to "md5"
	echo -e ${COLOR} Modify pg_hba.conf in /var/lib/pgsql/14/data to replace "ident" to "md5" ${NC}
	#Modify pg_hba.conf in /var/lib/pgsql/data to replace "ident" and "peer" to "md5"
	sed -i -e "s/peer/md5/g" /var/lib/pgsql/14/data/pg_hba.conf
	
	echo -e ${COLOR} Modify pg_hba.conf ident to md5 ${NC}
	sed -i -e "s/ident/md5/g" /var/lib/pgsql/14/data/pg_hba.conf
	
	#echo -e ${COLOR} Add TEXTTOEND to pg_hba.conf ${NC}
	sed -i -e "\$aTEXTTOEND" /var/lib/pgsql/14/data/pg_hba.conf
	
	#echo -e ${COLOR} Replace TEXTTOEND in pg_hba.conf ${NC}
	sed -i "s/TEXTTOEND/host all all 0.0.0.0\/0 md5/g" /var/lib/pgsql/14/data/pg_hba.conf
	
	echo -e ${COLOR} postgresql.conf to listen all addresses ${NC}
	sed -i -e "s/#listen_addresses/listen_addresses='*' #listen_addresses/g" /var/lib/pgsql/14/data/postgresql.conf
	
	echo -e ${COLOR} Set port ${NC}
	sed -i -e "s/#port/port = 5432 #port/g" /var/lib/pgsql/14/data/postgresql.conf
		
	sudo systemctl restart postgresql-14
	
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
	
	echo -e ${COLOR} Install Yarn ${NC}
	curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo
	curl --silent --location https://rpm.nodesource.com/setup_12.x | sudo bash -
	sudo yum install -y yarn
	yarn --version
	
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
	#git clone --single-branch --branch master https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
	#git clone --single-branch --branch sf4-php7 https://github.com/victorbrodsky/order-lab.git
	
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
	
	echo -e ${COLOR} Copy default-ssl.conf to /etc/httpd/conf.d ${NC}
	cp /usr/local/bin/order-lab/packer/default-ssl.conf /etc/httpd/conf.d
	
	echo -e ${COLOR} Copy env ${NC}
	cp /usr/local/bin/order-lab/packer/.env /usr/local/bin/order-lab/orderflex/
	
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
	cp /etc/opt/remi/php81/php.ini /etc/opt/remi/php81/php_ORIG.ini
	yes | cp /usr/local/bin/order-lab/packer/php.ini /etc/opt/remi/php81/
	
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

f_update_os
f_install_apache
f_install_postgresql14
f_install_php81
f_install_util
f_install_order
f_install_prepare
		   





	  