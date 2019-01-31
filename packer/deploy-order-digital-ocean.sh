#!/bin/bash
# A sample Bash script, by Ryan

#bash deploy-order-digital-ocean.sh 
#$1 API-TOKEN-FROM-STEP-1 
#$2 parameters.yml 
#$3 dbuser - optional
#$4 dbpass - optional
#$5 https - optional
#$6 domain_name.tld - optional
#$7 ssl_certificate.crt - optional
#$8 intermediate_certificate.ca-crt 
#$9 ssl.key - optional

#http: 
#1) bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword

#https: 
#1) copy sslcertificate sslprivatekey files to the packer folder
#2) bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword http domainname sslcertificate sslprivatekey
#3) select 'https' connection channel on the domainname/order/directory/admin/first-time-login-generation-init/ page 

apitoken=$1
parameters=$2
dbuser=$3
dbpass=$4

https=$5
domainname=$6
sslcertificate=$7
sslprivatekey=$8


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

echo "https=$https"
echo "domainname=$domainname"
echo "sslcertificate=$sslcertificate"
echo "sslprivatekey=$sslprivatekey"

echo "*** Verifying files presence ***"
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

if [ -z order-packer.json ]
then
    echo "order-packer.json not found."
	exit 0
fi

echo "*** Pre processing json file ***"
sed -i -e "s/api_token_bash_value/$apitoken/g" order-packer.json
sed -i -e "s/parameters_bash_file/$parameters/g" order-packer.json
sed -i -e "s/bash_dbuser/$dbuser/g" order-packer.json
sed -i -e "s/bash_dbpass/$dbpass/g" order-packer.json

#modify http.config file to insert virtual host for https
#https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-ubuntu-16-04
sed -i -e "s/bash_https/$https/g" order-packer.json
sed -i -e "s/bash_domainname/$domainname/g" order-packer.json
sed -i -e "s/bash_sslcertificate/$sslcertificate/g" order-packer.json
sed -i -e "s/bash_sslprivatekey/$sslprivatekey/g" order-packer.json


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

sed -i -e "s/$https/bash_https/g" order-packer.json
sed -i -e "s/$domainname/bash_domainname/g" order-packer.json
sed -i -e "s/$sslcertificate/bash_sslcertificate/g" order-packer.json
sed -i -e "s/$sslprivatekey/bash_sslprivatekey/g" order-packer.json


echo "*** Creating droplet ... ***"
DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)


echo "*** Starting firefox browser and creating admin user ***"
dropletinfos=( $DROPLET )
DROPLETIP="${dropletinfos[2]}"
echo "droplet IP=$DROPLETIP"

sleep 120

#DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"

if [ ! -z "$https" ] && [ "$https" = "https" ]
  then 	
	#check and delete existing domain DNS records www
	#1) doctl compute domain records list $domainname
	LIST=$(doctl compute domain records list $domainname | grep www | awk '{print $1}')
	#listinfo=( $LIST )
	#RECORDID="${listinfo[0]}"
	
	#2) doctl compute domain records delete $domainname record_id
	for recordid in $LIST; do
		echo "Delete RECORDID=$recordid"
		DELETERES=$(doctl compute domain records delete $domainname $recordid -v)
		echo "DELETERES=$DELETERES"
	done
  
	#doctl compute domain create domain_name --ip-address droplet_ip_address
	#doctl compute domain records create $domainname --record-type A --record-name www --record data $DROPLETIP -v
	DOMAIN=$(doctl compute domain records create $domainname --record-type A --record-name www --record-data $DROPLETIP -v)
	echo "DOMAIN=$DOMAIN"
	DROPLETIPWEB="http://www.$domainname/order/directory/admin/first-time-login-generation-init/https"
  else
    DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"
fi

echo "Trying to open a web browser... You can try to open a web browser manually and go to $DROPLETIPWEB"

xdg-open "$DROPLETIPWEB"

