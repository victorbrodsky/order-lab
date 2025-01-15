#!/bin/bash
#sync_tenants.sh

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#/usr/local/bin/order-lab-tenantapp1/orderflex
#/srv/order-lab-homepagemanager/orderflex

homedir=$1
#type: 'full'; doctrine migration status 'dbstatus'; 'composer' to run 'composer install'; 'python' to install python's env
type=$2

if [ -z "$homedir" ]
  then
    homedir="/srv"
    homedir="/usr/local/bin"
fi

if [ -z "$type" ]
  then
    type="full"
fi

bashpath="/usr/bin"

echo -e ${COLOR} homedir="$homedir" ${NC}
echo -e ${COLOR} bashpath="$bashpath" ${NC}
echo -e ${COLOR} type="$type" ${NC}

f_sync() {

    echo -e ${COLOR} check type="$type" ${NC}

    if [ -n "$type" ] && [ "$type" == "full" ]
    	then
    		echo -e ${COLOR} cd to "$1"${NC}
                cd "$homedir"/order-lab-$1/orderflex

            	echo -e ${COLOR} git pull for "$1" ${NC}
            	git pull

            	echo -e ${COLOR} bash deploy.sh for "$1" ${NC}
            	bash "$homedir"/order-lab-"$1"/orderflex/deploy.sh

            	echo -e ${COLOR} check migration status for "$1" ${NC}
                php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:status

                #echo -e ${COLOR} install python env for "$1" ${NC}
                #bash "$homedir"/order-lab-"$1"/packer/additional.sh
    	#else
    		#echo -e ${COLOR} Do not use multitenancy multitenant="$multitenant" ${NC}
    fi

    ### DB migration ###
    if [ -n "$type" ] && [ "$type" == "dbstatus" ]
        then
            echo -e ${COLOR} check migration status for "$1" ${NC}
            php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:status
        #else
        #    echo -e ${COLOR} type is empty ${NC}
    fi

    if [ -n "$type" ] && [ "$type" == "dbmigrate" ]
        then
            echo -e ${COLOR} check migration status for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:migrate
        #else
        #    echo -e ${COLOR} type is empty ${NC}
    fi
    ### EOF DB migration ###

    if [ -n "$type" ] && [ "$type" == "composer" ]
        then
            echo -e ${COLOR} composer install to ["$homedir"/order-lab-"$1"/orderflex] ${NC}
            COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer install --working-dir="$homedir"/order-lab-"$1"/orderflex --verbose
        #else
        #    echo -e ${COLOR} type is empty ${NC}
    fi

    if [ -n "$type" ] && [ "$type" == "python" ]
        then
            echo -e ${COLOR} install python env for "$1" ${NC}
            bash "$homedir"/order-lab-"$1"/packer/additional.sh "$homedir"/order-lab-"$1"
    fi

    #echo -e ${COLOR} cd to "$1"${NC}
    #cd "$homedir"/order-lab-$1/orderflex

	#echo -e ${COLOR} git pull for "$1" ${NC}
	#git pull

	#echo -e ${COLOR} bash deploy.sh for "$1" ${NC}
	#bash "$homedir"/order-lab-"$1"/orderflex/deploy.sh

	#echo -e ${COLOR} check migration status for "$1" ${NC}
    #php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:status
}


#f_sync homepagemanager
#f_sync tenantmanager
f_sync tenantappdemo
#f_sync tenantapptest
#f_sync tenantapp1
#f_sync tenantapp2

