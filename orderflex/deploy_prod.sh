#!/bin/sh

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


# Source: https://github.com/ZermattChris/Symfony2-SimpleDeployScripts
# Docs: see the accompanying README.md file.

# cd /path/to/simple
# ./deploy1

PARAM1="-full"
PARAM2="-nodb"
#PARAM1=$1
#PARAM2=$2

echo
echo "Example of full deploy(DB, casche, assetic updates and cache warmup): bash deploy_prod.sh"
echo "Example of fast update twig and js scripts: bash deploy_prod.sh -fast -withdb"
echo "Example of full deploy, except cache warmpup: bash deploy_prod.sh -fast"
echo

if [ $# -eq 0 ]
  then
    echo "No arguments supplied"
  else
    PARAM1=$1
fi

if [ $# -eq 0 ]
  then
    echo "No arguments supplied"
  else
    PARAM2=$2
fi

echo

if [[ $PARAM1 == "-fast" ]]
then
    echo "************* Start deploy fast (no cache warmup) *************"
else
    echo "************* Start deploy with cache warmup  *************"
fi

if [[ $PARAM2 == "-nodb" ]]
then
    echo "************* Start deploy without DB update *************"
else
    echo "************* Start deploy with DB update  *************"
fi

echo

##### Constants #####

PROJECT_NAME='ScanOrder Symfony2 Project'

# The location of the Symfony2 project you want to Deploy.
PROJECT_LOCAL_PATH=.


##### Functions #####
function prep(){
    echo "Preparing for Deploy..."

    #for production: git remote update, git pull
    #echo "*** Pull code from git repository ***"
    #git remote update
    #git pull

    #try to set permission
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/public
    chown -R apache:apache $PROJECT_LOCAL_PATH/public

    if [[ $PARAM2 != "-nodb" ]]
    then
        echo
        echo "*** Update tables in Doctrine DB ***"
        php -d memory_limit=1024M $PROJECT_LOCAL_PATH/bin/console doctrine:schema:update --force
        #php -d memory_limit=1024M $PROJECT_LOCAL_PATH/bin/console doctrine:schema:update

        echo "*** Validate Doctrine DB ***"
        php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:validate
    fi

    #echo "*** Create LEVENSHTEIN functions for fuzzy search ***"
    #php $PROJECT_LOCAL_PATH/bin/console jrk:levenshtein:install

    echo "*** Create a production build for Encore Webpack ***"
    yarn encore production

    echo "*** Install assets ***"
    #php bin/console assets:install public
    php $PROJECT_LOCAL_PATH/bin/console assets:install

    echo "*** Clear cache ***"
    #php bin/console cache:clear --no-warmup --env=prod
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --no-warmup --env=prod
    #php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug --no-warmup
    php $PROJECT_LOCAL_PATH/bin/console cache:clear --env=prod --no-debug

    #if [[ $PARAM1 != "-fast" ]]
    #then
    #    echo "*** Warmup cache ***"
    #    php -d memory_limit=1024M $PROJECT_LOCAL_PATH/bin/console cache:warmup --env=prod
    #fi


    echo "*** Set permissions ***"
    chmod a+x $PROJECT_LOCAL_PATH

    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/var/cache
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/var/log
    #chown -R www-data:www-data $PROJECT_LOCAL_PATH/public

    #chown -R apache:apache cache
    chown -R apache:apache $PROJECT_LOCAL_PATH/var/cache
    chown -R apache:apache $PROJECT_LOCAL_PATH/var/log
    chown -R apache:apache $PROJECT_LOCAL_PATH/public
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
