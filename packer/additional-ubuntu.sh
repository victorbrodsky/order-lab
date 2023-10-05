#!/bin/bash

COLOR='\033[1;36m'
NC='\033[0m' # No Color

echo -e ${COLOR} Installing env python ${NC}
cd /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/
sudo apt install -y python3-venv
python3 -m venv venv
source venv/bin/activate
#sudo pip3 install -r requirements.txt
pip install --upgrade pip
python -m pip install -r requirements.txt
cd /usr/local/bin/order-lab/orderflex/

