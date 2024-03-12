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
function execute()
{
    echo "Execute default Migration..."
    php $PROJECT_LOCAL_PATH/bin/console doctrine:migrations:migrate --all-or-nothing

    #echo "Execute systemdb Migration..."
    #php $PROJECT_LOCAL_PATH/bin/console doctrine:migrations:migrate --em=systemdb --all-or-nothing
}


######## Run ########
echo
echo "************* Execute all migration $PROJECT_NAME *************"
echo

# Prepare for Deploy by clearing the local cache and dumping assetic assets.
execute

echo "Execute all migration complete."
echo
exit 0