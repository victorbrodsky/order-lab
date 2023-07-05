#!/bin/bash
# Script author: Danie Pham
# Script site: https://www.writebash.com
# Script date: 22-03-2018
# Script ver: 1.0
# Script use to install LAMP stack on CentOS 7.x
#--------------------------------------------------
# Software version:
# 1. OS: CentOS 7.4.1708 (Core) 64bit.
# 2. Apache: Apache/2.4.6 (CentOS)
# 3. MariaDB: 10.2.13-MariaDB
# 4. PHP 7: PHP 7.2.3 (cli)
#--------------------------------------------------
# List function:
# 1. f_check_root: check to make sure script can be run by user root
# 2. f_disable_selinux: check selinux status, disable it if it's enforcing
# 3. f_update_os: update all the packages
# 4. f_install_lamp: funtion to install LAMP stack
# 5. f_open_port: config firewalld to open port 80, 443
# 6. f_sub_main: function use to call the main part of installation
# 7. f_main: the main function, add your functions to this place


# Function check user root
f_check_root () {
    if (( $EUID == 0 )); then
        # If user is root, continue to function f_sub_main
        f_sub_main
    else
        # If user not is root, print message and exit script
        echo "Please run this script by user root !"
        exit
    fi
}

# Function to disable SELinux
f_disable_selinux () {
    SE=`cat /etc/selinux/config | grep ^SELINUX= | awk -F'=' '{print $2}'`
    echo "Checking SELinux status ..."
    echo ""
    sleep 1

    if [[ "$SE" == "enforcing" ]]; then
        sed -i 's|SELINUX=enforcing|SELINUX=disabled|g' /etc/selinux/config
        echo "Disable SElinux and reboot after 5s. Press Ctrl+C to stop script."
        echo "After system reboot, please run script again."
        echo ""
        sleep 5
        reboot
    fi
}

# Function update os
f_update_os () {
    echo "Starting update os ..."
    sleep 1

    yum update
    yum upgrade -y

    echo ""
    sleep 1
}

# Function install LAMP stack
f_install_lamp () {
    ########## INSTALL APACHE ##########
    echo "Installing apache ..."
    sleep 1

    yum install httpd -y

    # This part is optimize for server 2GB RAM
    cp /etc/httpd/conf/httpd.conf /etc/httpd/conf/httpd.conf.original
    sed -i '/<IfModule prefork.c/,/<\/IfModule/{//!d}' /etc/httpd/conf/httpd.conf
    sed -i '/<IfModule prefork.c/a\ StartServers              4\n MinSpareServers           20\n MaxSpareServers           40\n MaxClients         200\n MaxRequestsPerChild    4500' /etc/httpd/conf/httpd.conf

    # Enable and start httpd service
    systemctl enable httpd.service
    systemctl restart httpd.service

    ########## INSTALL MARIADB ##########
    echo "Add MariaDB to repositories ..."
    sleep 1

    # Add MariaDB repository
    cat > /etc/yum.repos.d/MariaDB.repo <<"EOF"
[mariadb]
name = MariaDB
baseurl = http://yum.mariadb.org/10.2/centos7-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
EOF

    # Update new package
    echo "Update package for MariaDB ..."
    sleep 1
    yum update -y

    # Start install MariaDB
    echo "Installing MariaDB server ..."
    sleep 1
    yum install MariaDB-server MariaDB-client -y

    # Enable and start mysql service
    systemctl enable mariadb
    systemctl start mariadb
    echo ""
    sleep 1

    ########## INSTALL PHP7 ##########
    yum install epel-release -y
    yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y
    yum install yum-utils -y
    yum-config-manager --enable remi-php72
    yum install php php-mcrypt php-cli php-gd php-curl php-mysql php-ldap php-zip php-fileinfo php-pear -y

    # Config to fix error Apache not load PHP file
    chown -R apache:apache /var/www
    sed -i '/<Directory \/>/,/<\/Directory/{//!d}' /etc/httpd/conf/httpd.conf
    sed -i '/<Directory \/>/a\    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted' /etc/httpd/conf/httpd.conf

    # Restart Apache
    systemctl restart httpd
}

# Function enable port 80,433 in IPtables
f_open_port () {
    firewall-cmd --permanent --add-service=http
    firewall-cmd --permanent --add-service=https
    firewall-cmd --permanent --add-port=80/tcp
    firewall-cmd --permanent --add-port=443/tcp
    firewall-cmd --reload
}

# The sub main function, use to call neccessary functions of installation
f_sub_main () {
    f_disable_selinux
    f_update_os
    f_install_lamp
    f_open_port

    echo "<?php phpinfo(); ?>" > /var/www/html/info.php
    echo ""
    echo ""
    echo "Please run command to secure MariaDB: mysql_secure_installation"
    echo "You can access http://YOUR-SERVER-IP/info.php to see more informations about PHP"
    sleep 1
}

# The main function
f_main () {
    f_check_root
}
f_main

exit