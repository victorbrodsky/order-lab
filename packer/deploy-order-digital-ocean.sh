#!/bin/bash
# A sample Bash script, by Ryan

apitoken=$1
parameters=$2
dbuser=$3
dbpass=$4

if [ -z "$dbuser" ]
  then 	
    dbuser='symfony'
fi

if [ -z "$dbpass" ]
  then 	
    dbpass='symfony'
fi


echo "*** Deploy order to Digital Ocean ***"

echo "api_token=$apitoken" 
echo "parameters=$parameters" 
echo "dbuser=$dbuser"
echo "dbpass=$dbpass"

if [ -z "$apitoken" ]
  then 	
    echo "Error: no token is provided"
    exit 0
fi

if [ -z "$parameters" ]
  then 	
    echo "Error: no parameter file is provided"
    exit 0
fi

echo "*** Pre processing json file ***"
sed -i -e "s/api_token_bash_value/$apitoken/g" order-packer.json
sed -i -e "s/parameters_bash_file/$parameters/g" order-packer.json
sed -i -e "s/bash_dbuser/$dbuser/g" order-packer.json
sed -i -e "s/bash_dbpass/$dbpass/g" order-packer.json



echo "*** Building VM image ... ***"
packer build order-packer.json


echo "*** Getting image ID ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed

LASTLINE=$(doctl compute image list | tail -1)
#echo "LASTLINE=$LASTLINE"
vars=( $LASTLINE )
IMAGEID=${vars[0]}
IMAGENAME=${vars[1]}
echo "image ID=$IMAGEID; name=$IMAGENAME"


echo "*** Post processing json file ***"
sed -i -e "s/$apitoken/api_token_bash_value/g" order-packer.json
sed -i -e "s/$parameters/parameters_bash_file/g" order-packer.json
sed -i -e "s/$dbuser/bash_dbuser/g" order-packer.json
sed -i -e "s/$dbpass/bash_dbpass/g" order-packer.json


echo "*** Creating droplet ... ***"
DROPLET=$(doctl compute droplet create $IMAGENAME --size 1gb --image $IMAGEID --region nyc3 --wait | tail -1)


echo "*** Starting firefox browser and creating admin user ***"
dropletinfos=( $DROPLET )
DROPLETIP="${dropletinfos[2]}"
echo "droplet IP=$DROPLETIP"

sleep 30

DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"

echo "Trying to open a web browser... You can try to open a web browser manually and go to $DROPLETIPWEB"

xdg-open "$DROPLETIPWEB"

