#!/bin/bash

#Create python environment for postgres-manage-python and for scrapper

COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ -z "$bashpath" ]
  then
    bashpath=$1
fi

if [ -z "$bashpath" ]; then
    #bashpath="/usr/local/bin"
    bashpath="/srv/order-lab"
fi

#bashpath="/usr/local/bin"
#bashpath="/srv"

echo additional.sh: bashpath=$bashpath

#Create python environment for postgres-manage-python
#cd /srv/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/
#folder: /srv/order-lab/packer/
echo -e ${COLOR} Installing env python for postgres-manage-python to "$bashpath" ${NC}
cd "$bashpath"/utils/db-manage/postgres-manage-python/
python3 -m venv venv
source venv/bin/activate
#sudo pip3 install -r requirements.txt
pip install --upgrade pip
python -m pip install -r "$bashpath"/utils/db-manage/postgres-manage-python/requirements.txt
deactivate
cd "$bashpath"/orderflex/

#Create python environment for scraper
echo -e ${COLOR} Installing env python for scraper to "$bashpath" ${NC}
#cd "$bashpath"/utils/scrapper/
#ls -a
#python3 -m venv venv
#source venv/bin/activate
#pip install --upgrade pip
#python -m pip install -r "$bashpath"/utils/scraper/requirements.txt
#deactivate
#cd "$bashpath"/orderflex/

# Define the target folder
TARGET_DIR="$bashpath/utils/scraper"
ENV_NAME="venv"

# Navigate to the target directory
cd "$TARGET_DIR" || { echo "Directory not found"; exit 1; }

# Create the virtual environment
python -m venv "$ENV_NAME"

# Activate the environment (this works for Linux/macOS)
source "$TARGET_DIR/$ENV_NAME/bin/activate"

echo "Virtual environment '$ENV_NAME' created and activated in '$TARGET_DIR'"

python -m pip install -r "$TARGET_DIR/requirements.txt

deactivate

echo "Deactivate virtual environment '$ENV_NAME' in '$TARGET_DIR'"

