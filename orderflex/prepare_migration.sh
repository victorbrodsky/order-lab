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

PROJECT_NAME='ScanOrder Symfony Project'

# The location of the Symfony project you want to Deploy.
PROJECT_LOCAL_PATH=.

##### Functions #####
function prep()
{
    echo "Preparing for Migration..."

    echo "*** cache:clear ***"
    php $PROJECT_LOCAL_PATH/bin/console cache:clear

    echo "*** doctrine:cache:clear-metadata ***"
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-collection-region
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-entity-region
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-metadata
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-query
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-query-region
    php $PROJECT_LOCAL_PATH/bin/console doctrine:cache:clear-result
    php $PROJECT_LOCAL_PATH/bin/console doctrine:migrations:sync-metadata-storage

    echo "*** doctrine:schema:validate ***"
    php $PROJECT_LOCAL_PATH/bin/console doctrine:schema:validate

    echo "*** doctrine:migrations:status ***"
    php $PROJECT_LOCAL_PATH/bin/console doctrine:migrations:status
}


######## Run ########
echo
echo "************* Preparing for migration $PROJECT_NAME *************"
echo

# Prepare for Deploy by clearing the local cache and dumping assetic assets.
prep

echo "Preparing for migration complete."
echo
exit 0