#!/bin/bash
#sync_tenants.sh

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#/usr/local/bin/order-lab-tenantapp1/orderflex
#/srv/order-lab-homepagemanager/orderflex

homedir=$1

if [ -z "$homedir" ]
  then
    homedir='/srv'
fi

bashpath="/usr/bin"


echo -e ${COLOR} homedir="$homedir" ${NC}
echo -e ${COLOR} bashpath="$bashpath" ${NC}

f_sync() {
    echo -e ${COLOR} cd to "$1"${NC}
    cd "$homedir"/order-lab-$1

	echo -e ${COLOR} git pull for "$1" ${NC}
	git pull

	echo -e ${COLOR} bash deploy.sh for "$1" ${NC}
	bash "$bashpath"/order-lab-"$1"/orderflex/deploy.sh
}


f_sync homepagemanager
f_sync tenantmanager
f_sync tenantappdemo
f_sync tenantapptest
f_sync tenantapp1
f_sync tenantapp2

