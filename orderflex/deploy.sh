#    Copyright 2017 Cornell University
#
#    Licensed under the Apache License, Version 2.0 (the "License");
#    you may not use this file except in compliance with the License.
#    You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
#    Unless required by applicable law or agreed to in writing, software
#    distributed under the License is distributed on an "AS IS" BASIS,
#    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#    See the License for the specific language governing permissions and
#    limitations under the License.

#!/bin/sh

# Source: https://github.com/ZermattChris/Symfony2-SimpleDeployScripts
# Docs: see the accompanying README.md file.

# cd /path/to/simple
# ./deploy1


##### Constants #####

PROJECT_NAME='ScanOrder Symfony Project'

# The location of the Symfony project you want to Deploy.
PROJECT_LOCAL_PATH=.

WEB_USER='apache:apache'

if id "apache" >/dev/null 2>&1; then
    echo 'apache user found'
    WEB_USER='apache:apache'
else
    echo 'apache user not found'
fi

if id "www-data" >/dev/null 2>&1; then
    echo 'www-data user found'
    WEB_USER='www-data:www-data'
else
    echo 'www-data user not found'
fi

echo WEB_USER="$WEB_USER"

#$PROJECT_LOCAL_PATH/bin/console => php bin/console

##### Functions #####
function prep()
{
    echo "Preparing for Deploy..."

    #try to set permission
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/public
    #chown -R apache:apache $PROJECT_LOCAL_PATH/public
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/public

    #for production: git remote update, git pull
    #echo "*** Pull code from git repository ***"
    #git remote update
    #git pull

    #echo "*** Update tables in Doctrine DB ***"
    #php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:update --force
    #php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:update

    echo "*** Validate Doctrine DB ***"
    php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:validate
    #php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:validate --em systemdb

    #By default, console commands run in the dev environment
    #console: php bin/console assets:install
    echo "*** Install assets ***"
    php $PROJECT_LOCAL_PATH/bin/console assets:install

    #echo "*** Install yarn package.json based on yarn.lock ***"
    #Run npm install --force to update package-lock.js if a new package installed by yarn, i.e. 'yarn add dotenv'
    #Run after modified package-lock.js: yarn install --frozen-lockfile
    echo "*** Create a production build for Encore Webpack ***"
    yarn encore production

    echo "********* Prepare development/testing *********"

    #echo "*** Clear cache ***"
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --no-warmup

    #php bin/console cache:clear --no-warmup --env=prod
    echo "*** Clear cache with warmup ***"
    php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug

    #echo "*** Dump assets ***"
    #php $PROJECT_LOCAL_PATH/bin/console assetic:dump


    #By default, console commands run in the dev environment
    #echo "********* Prepare production *********"

    #echo "*** Clear cache ***"
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug --no-warmup
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug --no-warmup
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug

    #echo "*** Warmup cache ***"
    #php -d memory_limit=1024M $PROJECT_LOCAL_PATH/bin/console cache:warmup --env=prod

    #echo "*** Dump assets for production***"
    #php $PROJECT_LOCAL_PATH/bin/console assetic:dump --env=prod --no-debug

    echo "*** Set permissions ***"
    chmod a+x $PROJECT_LOCAL_PATH

    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/var/cache
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/var/log
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/public

    #chown -R apache:apache $PROJECT_LOCAL_PATH/var/cache
    #chown -R apache:apache $PROJECT_LOCAL_PATH/var/log
    #chown -R apache:apache $PROJECT_LOCAL_PATH/public
    #chown -R apache:apache $PROJECT_LOCAL_PATH/../backup

    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/var
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/var/cache
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/var/log
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/public
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/../backup

    chmod 744 $PROJECT_LOCAL_PATH/../backup/pg_backup.sh
    chown postgres $PROJECT_LOCAL_PATH/../backup/pg_backup.sh
    chgrp postgres $PROJECT_LOCAL_PATH/../backup/pg_backup.sh

    chmod 744 $PROJECT_LOCAL_PATH/../backup/alert_dba
    chown postgres $PROJECT_LOCAL_PATH/../backup/alert_dba
    chgrp postgres $PROJECT_LOCAL_PATH/../backup/alert_dba

    echo "*** Set permissions to restart haproxy and httpd ***"
    chmod a+x $PROJECT_LOCAL_PATH/../utils/executables
    chown -R "$WEB_USER" $PROJECT_LOCAL_PATH/../utils/executables
    chmod 744 $PROJECT_LOCAL_PATH/../utils/executables/haproxy-restart.sh
    chmod 744 $PROJECT_LOCAL_PATH/../utils/executables/httpd-restart.sh
    #Danger: required for multinenacy
    echo "*** Set permissions for haproxy and httpd config ***"
    chown -R "$WEB_USER" /etc/httpd/conf/*.conf
    chmod 744 /etc/httpd/conf/*.conf
    chown -R "$WEB_USER" /etc/haproxy/haproxy.cfg
    chmod 744 /etc/haproxy/haproxy.cfg

    chown -R "$WEB_USER" .git/
}


######## Run ########
echo
echo "************* Deploying $PROJECT_NAME *************"
echo

# Prepare for Deploy by clearing the local cache and dumping assetic assets.
prep

echo "Deploy complete."
echo
exit 0