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

######## ./bin/simple-phpunit wrapper ########

#PARAM1="-tests"
#PARAM2="-nodata"
PARAM1=""
PARAM2=""

TEST_NAME='ORDER Project Test Suite'
PROJECT_LOCAL_PATH=.

echo
echo "************* Testing $TEST_NAME *************"
echo

if [ $# -eq 0 ]
  then
    echo "No specific tests supplied: test will run with all available tests"
    TESTENVPARAM=""
  else
    PARAM1=$1
    # TESTENV=nodata ./bin/simple-phpunit
    TESTENVPARAM="TESTENV=nodata"
fi

if [ $# -eq 0 ]
  then
    echo "No nodata supplied: test will run assuming data is not empty"
  else
    PARAM2=$2
fi

echo "$PARAM1"
echo "$PARAM2"
echo "$TESTENVPARAM"

$TESTENVPARAM $PROJECT_LOCAL_PATH/bin/simple-phpunit $PARAM2

echo
echo
echo "Test complete."
echo
exit 0