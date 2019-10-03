#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)

echo @### Install yum-utils and enable epel repository ###
sudo yum -y install epel-release


echo @### PHP1: sudo yum-config-manager --enable remi-php72 ###
sudo yum-config-manager --enable remi-php72 -y

echo @### PHP2: sudo yum install php72 -y ###
sudo yum install yum-utils -y
sudo yum update
sudo yum search php72 | more
sudo yum install -y php72 

#echo @### PHP3: sudo yum install php-common -y ###
#sudo yum update
#sudo yum install php-common -y

echo @### PHP4: sudo yum install php-cli and others -y ###
sudo yum install -y php72 php72-php-fpm php72-php-gd php72-php-json php72-php-mbstring php72-php-mysqlnd php72-php-xml php72-php-xmlrpc php72-php-opcache
sudo systemctl enable php72-php-fpm.service
sudo systemctl start php72-php-fpm.service

echo @### Install Apache ###
sudo yum install httpd -y
sudo systemctl enable httpd.service
sudo systemctl start httpd.service
sudo systemctl status httpd.service


echo @### Install the repository RPM, client and server packages ###		
sudo yum install https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm -y
#No package postgresql11 available
yum install postgresql12 -y
yum install postgresql12-server -y
#sudo yum -y install postgresql11 postgresql11-server postgresql11-contrib postgresql11-libs

#echo @### (use this???) /usr/pgsql-11/bin/postgresql-11-setup initdb ###
echo @### Optionally initialize the database and enable automatic start ###	
/usr/pgsql-12/bin/postgresql-12-setup initdb
systemctl enable postgresql-12
systemctl start postgresql-12

echo @### Create DB and create user ###
sudo -Hiu postgres createdb scanorder
sudo -Hiu postgres psql -c "CREATE USER bash_dbuser WITH PASSWORD 'bash_dbuser'"
sudo -Hiu postgres psql -c "ALTER USER bash_dbuser WITH SUPERUSER"
sudo -Hiu postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE scanorder to bash_dbuser"

echo @### Install Git ###		
sudo yum install git -y	

echo @### Install wkhtmltopdf, libreoffice, ghostscript, pdftk ###
sudo yum update
sudo yum install -y xvfb libfontconfig wkhtmltopdf	
sudo yum install -y libreoffice	
sudo yum install -y ghostscript
sudo yum install -y pdftk      	
		   
echo @### Clone ORDER and copy config and php.ini files, install composer ###
ssh-keyscan github.com >> ~/.ssh/known_hosts
cd /usr/local/bin/
git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab
sudo chmod a+x /usr/local/bin/order-lab
sudo chown -R www-data:www-data /usr/local/bin/order-lab

echo @### Copy 000-default.conf to /etc/httpd/conf.d ###
cp /usr/local/bin/order-lab/packer/000-default.conf /etc/httpd/conf.d

echo @### Copy php.ini to /etc/httpd/conf.d ###
#/etc/opt/remi/php72/ or /etc/
cp /usr/local/bin/order-lab/packer/php.ini /etc/
sudo a2enmod rewrite
#sudo service apache2 restart
sudo systemctl restart httpd.service
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer      		   
		   
		   

	  