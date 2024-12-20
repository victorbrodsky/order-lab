#!/bin/bash

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ -z "$bashpath" ]
  then
    bashpath=$1
fi

if [ -z "$bashpath" ]; then
    bashpath="/usr/local/bin"
fi

#bashpath="/usr/local/bin"
#bashpath="/srv"

echo bashpath=$bashpath

#cd /srv/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/
echo -e ${COLOR} Installing env python to "$bashpath" ${NC}
cd "$bashpath"/utils/db-manage/postgres-manage-python/
python3 -m venv venv
source venv/bin/activate
#sudo pip3 install -r requirements.txt
pip install --upgrade pip
python -m pip install -r requirements.txt
cd "$bashpath"/orderflex/

