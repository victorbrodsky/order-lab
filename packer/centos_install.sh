#!/bin/bash
# CentOs installation script (Rhel 7, PHP 7.2, Postgresql)

echo @### Install yum-utils and enable epel repository ###
sudo yum -y install epel-release


echo @### RUN1: sudo yum-config-manager --enable remi-php72 ###
sudo yum-config-manager --enable remi-php72

echo @### RUN2: sudo yum install php -y ###
sudo yum update
sudo yum install php -y









	  