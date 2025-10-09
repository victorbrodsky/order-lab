#!/bin/bash
#sync_tenants.sh

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#Usage: bash sync_tenants.sh basic /srv

#/usr/local/bin/order-lab-tenantapp1/orderflex
#/srv/order-lab-homepagemanager/orderflex

type=$1

homedir=$2
#type: 'basic'; sync source code, run deploy.sh, show doctrine migration status 'dbstatus'.


if [ -z "$type" ]
  then
    type="basic"
fi

if [ -z "$homedir" ]
  then
    homedir="/srv"
    #homedir="/usr/local/bin"
fi


bashpath="/usr/bin"

echo -e ${COLOR} homedir="$homedir" ${NC}
echo -e ${COLOR} bashpath="$bashpath" ${NC}
echo -e ${COLOR} type="$type" ${NC}

f_sync() {

    echo -e ${COLOR} check type="$type" ${NC}

    if [ -n "$type" ] && [ "$type" == "basic" ]
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

    if [ -n "$type" ] && [ "$type" == "full" ]
        then
            echo -e ${COLOR} cd to "$1"${NC}
            cd "$homedir"/order-lab-$1/orderflex

            echo -e ${COLOR} git pull for "$1" ${NC}
            git pull

            echo -e ${COLOR} check migration status for "$1" ${NC}
            php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:status

            echo -e ${COLOR} check migration status for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:migrate

            echo -e ${COLOR} yarn install --frozen-lockfile for "$1" ${NC}
            yarn install --frozen-lockfile

            echo -e ${COLOR} bash deploy.sh for "$1" ${NC}
            bash "$homedir"/order-lab-"$1"/orderflex/deploy.sh
    fi

    if [ -n "$type" ] && [ "$type" == "yarn" ]
        then
            echo -e ${COLOR} cd to "$1"${NC}
            cd "$homedir"/order-lab-$1/orderflex

            echo -e ${COLOR} yarn install --frozen-lockfile for "$1" ${NC}
            yarn install --frozen-lockfile
    fi

    if [ -n "$type" ] && [ "$type" == "sync" ]
        then
            echo -e ${COLOR} cd to "$1"${NC}
            cd "$homedir"/order-lab-$1/orderflex

            echo -e ${COLOR} git pull for "$1" ${NC}
            git pull
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

    if [ -n "$type" ] && [ "$type" == "addallversions" ]
            then
                echo -e ${COLOR} check migration status for "$1" ${NC}
                yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:version --add --all
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

    if [ -n "$type" ] && [ "$type" == "dbconfig" ]
        then
            echo -e ${COLOR} Install db.config for python postgres-manage-python for order-lab-"$1" ${NC}
            cp "$homedir"/order-lab-"$1"/utils/db-manage/postgres-manage-python/sample.config "$homedir"/order-lab-"$1"/utils/db-manage/postgres-manage-python/db.config
            sed -i -e "s/dbname/$1/g" "$homedir"/order-lab-"$1"/utils/db-manage/postgres-manage-python/db.config
            sed -i -e "s/dbusername/symfony/g" "$homedir"/order-lab-"$1"/utils/db-manage/postgres-manage-python/db.config
            sed -i -e "s/dbuserpassword/symfony/g" "$homedir"/order-lab-"$1"/utils/db-manage/postgres-manage-python/db.config
    fi

    if [ -n "$type" ] && [ "$type" == "secret" ]
        then
            echo -e ${COLOR} Update secret key in parameters.yml for "$1" ${NC}
            bash "$homedir"/order-lab-"$1"/orderflex/secret_update.sh "$homedir"/order-lab-"$1"

            echo -e ${COLOR} bash deploy.sh for "$1" ${NC}
            bash "$homedir"/order-lab-"$1"/orderflex/deploy.sh
    fi

    if [ -n "$type" ] && [ "$type" == "createdb" ]
        then
            echo -e ${COLOR} Create db for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:database:create

            echo -e ${COLOR} php bin/console doctrine:migration:sync-metadata-storage for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migration:sync-metadata-storage

            echo -e ${COLOR} php bin/console doctrine:schema:update --force for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:schema:update --force

            echo -e ${COLOR} php bin/console doctrine:migrations:version --add --all for "$1" ${NC}
            yes | php "$homedir"/order-lab-"$1"/orderflex/bin/console doctrine:migrations:version --add --all
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


f_sync homepagemanager
f_sync tenantmanager
f_sync tenantappdemo
f_sync tenantapptest
f_sync tenantapp1
f_sync tenantapp2

