#!/bin/bash

#https://certbot.eff.org/instructions?ws=apache&os=centosrhel8
#bash /usr/local/bin/order-lab/packer/install-certbot.sh view.online installcertbot oli2002@med.cornell.edu apitoken
#run script from local PC: ssh root@159.203.85.84 'bash -s' < ./install-certbot.sh need to enter root password

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ -z "$userpass" ]
  then
    userpass=$1
fi

echo userpass=$userpass

echo Script prepare_certbot.sh: Start ...

if [ -z "$userpass" ]
  then
    #Make sure to change the default password after installation
    userpass='1234567890'
fi

echo Use userpass=$userpass

$echo "$userpass" | sudo ls -la /root
exit 0

OSNAME=""
if cat /etc/*release | grep ^NAME | grep CentOS; then
    OSNAME="CentOS"
 elif cat /etc/*release | grep ^NAME | grep Red; then
    OSNAME="Red"
 elif cat /etc/*release | grep ^NAME | grep Ubuntu; then
    OSNAME="Ubuntu"
 elif cat /etc/*release | grep ^NAME | grep Alma; then
    OSNAME="Alma"
 else
    echo "OS NOT DETECTED, couldn't install packages"
    exit 1;
 fi
 echo "==============================================="
 echo "Installing packages on $OSNAME"
 echo "==============================================="

echo -e ${COLOR} Create a New Sudo-enabled User. All members of the wheel group have full sudo access ${NC}
#adduser adminuser

echo -e ${COLOR} Create password ${NC}
#passwd adminuser

echo -e ${COLOR} Add the user to the wheel group ${NC}
if [ "$OSNAME" = "Ubuntu" ]
  then
      echo "==============================================="
      echo "Use Ubuntu"
      echo "==============================================="
      #usermod -aG sudo adminuser
      useradd -p $(openssl passwd -1 "$userpass") adminuser -s /bin/bash -G sudo
  else
      echo "==============================================="
      echo "Use Centos, Alma"
      echo "==============================================="
      #usermod -aG wheel adminuser
      useradd -p $(openssl passwd -1 "$userpass") adminuser -s /bin/bash -G wheel
fi

#Testing:
#The first time you use sudo in a session, you will be prompted for the password of that user’s account.
echo -e ${COLOR} Init sudo user. The first time you use sudo in a session, you will be prompted for the password of that user account. ${NC}
su - adminuser

echo -e ${COLOR} Testing sudo user by  ${NC}
$echo "$userpass" | su - adminuser #sudo ls -la /root

#check sudo access for a specific user
#sudo -l -U adminuser

#To gain root shell, enter
#sudo -s

#use the -S switch which reads the password from STDIN:
#$echo <password> | sudo -S <command>

#Swicth to root
#sudo su -

#List of All Users
#less /etc/passwd

#check sudo access for a specific user
#sudo -l -U sk

#Delete user
#userdel adminuser

#Delete user force
#userdel -r adminuser
#userdel -f adminuser

#log out the user and kill all user’s running processes
#sudo killall -u adminuser
