#!/bin/bash

echo -e "${COLOR} Installing env python for "
cd /usr/local/bin/order-lab/utils/db-manage/postgres-manage-python/
python3 -m venv venv
source venv/bin/activate
#sudo pip3 install -r requirements.txt
python -m pip install -r requirements.txt
cd /usr/local/bin/order-lab/orderflex/

