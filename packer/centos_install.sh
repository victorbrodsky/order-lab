#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)

echo @### Install yum-utils and enable epel repository ###
sudo yum -y install epel-release


echo @### PHP1: sudo yum-config-manager --enable remi-php72 ###
sudo yum-config-manager --enable remi-php72

echo @### PHP2: sudo yum install php -y ###
sudo yum update
sudo yum install php -y

echo @### PHP3: sudo yum install php-common -y ###
sudo yum update
sudo yum install php-common -y

echo @### PHP4: sudo yum install php-cli and others -y ###
sudo yum update
sudo yum install php-cli php-pear php-pdo php-mysqlnd php-gd php-mbstring php-mcrypt php-xml php-curl -y

echo @### Install Apache ###
sudo yum install httpd -y
sudo systemctl start httpd.service
sudo systemctl enable httpd.service

echo @### Install postgresql and pgsql ###		
sudo yum install https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm
sudo yum -y install postgresql11 postgresql11-server postgresql11-contrib postgresql11-libs

#echo @### (use this???) /usr/pgsql-11/bin/postgresql-11-setup initdb ###
sudo systemctl start postgresql-11.service
sudo systemctl enable postgresql-11.service 



	  