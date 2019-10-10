#!/bin/bash
# ORDER installation script

#bash deploy-order-digital-ocean.sh 
#--token API-TOKEN-FROM-STEP-1 
#--os operational system: centos or ubuntu (default)
#-p parameters.yml 
#-dbuser - optional (default symfony)
#-dbpass - optional (default symfony)
#-protocol - optional (default http)
#--domainname domain_name.tld - optional
#--sslcertificate ssl_certificate.crt - optional
#--sslprivatekey intermediate_certificate.ca-crt 


#os - centos or ubuntu
#apitoken=$1
#parameters=$2
#dbuser=$3
#dbpass=$4

#protocol=$5
#domainname=$6
#sslcertificate=$7
#sslprivatekey=$8



#$ bash deploy_test.sh --token apitoken --os centos --parameters parameters.yml --dbuser symfony --dbpass symfony --protocol http --domainname domainname --sslcertificate localhost.crt --sslprivatekey localhost.key

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
    -t|--token)
		apitoken="$2"
		shift # past argument
		shift # past value
    ;;
	-o|--os)
		os="$2"
		shift # past argument
		shift # past value
    ;;
    -s|--parameters)
		parameters="$2"
		shift # past argument
		shift # past value
    ;;
    -u|--dbuser)
		dbuser="$2"
		shift # past argument
		shift # past value
    ;;
	-p|--dbpass)
		dbpass="$2"
		shift # past argument
		shift # past value
    ;;
	-l|--protocol)
		protocol="$2"
		shift # past argument
		shift # past value
    ;;
	-d|--domainname)
		domainname="$2"
		shift # past argument
		shift # past value
    ;;
	-c|--sslcertificate)
		sslcertificate="$2"
		shift # past argument
		shift # past value
    ;;
	-k|--sslprivatekey)
		sslprivatekey="$2"
		shift # past argument
		shift # past value
    ;;
    --default)
		DEFAULT=YES
		shift # past argument
    ;;
    *)    # unknown option
		POSITIONAL+=("$1") # save it in an array for later
		shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

if [ -z "$os" ]
  then 	
    os='ubuntu'
fi

if [ -z "$dbuser" ]
  then 	
    dbuser='symfony'
fi

if [ -z "$dbpass" ]
  then 	
    dbpass='symfony'
fi

if [ -z "$parameters" ]
  then 	
    parameters='parameters.yml'
fi

if [ -z "$protocol" ]
  then 	
    protocol='http'
fi

if [ -z "$domainname" ]
  then 	
    domainname='domainname'
fi

if [ -z "$sslcertificate" ]
  then 	
    sslcertificate='localhost.crt'
fi

if [ -z "$sslprivatekey" ]
  then 	
    sslprivatekey='localhost.key'
fi

echo "api_token=$apitoken" 
echo "os=$os"
echo "parameters=$parameters" 
echo "dbuser=$dbuser"
echo "dbpass=$dbpass"

echo "protocol=$protocol"
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

if [ "$os" = "centos" ]
  then 	
	ORDERPACKERJSON="order-packer-centos.json"
fi
if [ "$os" = "centosonly" ]
  then 	
	ORDERPACKERJSON="order-packer-centos-only.json"
fi

if [ "$os" = "ubuntu" ]
  then 	
	ORDERPACKERJSON="order-packer-ubuntu.json"
fi

if [ -z "$ORDERPACKERJSON" ]
then
    echo "order-packer.json not found. Use default order-packer-ubuntu.json"
	ORDERPACKERJSON="order-packer-ubuntu.json"
fi

echo "ORDERPACKERJSON=$ORDERPACKERJSON"

if [ -f "$ORDERPACKERJSON" ]; then
    echo "$ORDERPACKERJSON exist"
else 
    echo "$ORDERPACKERJSON does not exist"
	exit 0;
fi


echo "*** Pre processing json file ***"
sed -i -e "s/api_token_bash_value/$apitoken/g" "$ORDERPACKERJSON"
sed -i -e "s/parameters_bash_file/$parameters/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbpass/$dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" parameters.yml
sed -i -e "s/bash_dbpass/$dbpass/g" parameters.yml

sed -i -e "s/bash_domainname/$domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslcertificate/$sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslprivatekey/$sslprivatekey/g" "$ORDERPACKERJSON"


echo "*** Building VM image from packer=[$ORDERPACKERJSON] ... ***"
packer build "$ORDERPACKERJSON"


echo "*** Getting image ID ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed

LASTLINE=$(doctl compute image list | tail -1)
#echo "LASTLINE=$LASTLINE"
vars=( $LASTLINE )
IMAGEID=${vars[0]}
IMAGENAME=${vars[1]}
echo "image ID=$IMAGEID; name=$IMAGENAME"


echo "*** Post processing json file ***"
sed -i -e "s/$apitoken/api_token_bash_value/g" "$ORDERPACKERJSON"
sed -i -e "s/$parameters/parameters_bash_file/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbpass/bash_dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" parameters.yml
sed -i -e "s/$dbpass/bash_dbpass/g" parameters.yml

sed -i -e "s/$domainname/bash_domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslcertificate/bash_sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslprivatekey/bash_sslprivatekey/g" "$ORDERPACKERJSON"


echo "*** Creating droplet ... ***"
DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)


echo "*** Starting firefox browser and creating admin user ***"
dropletinfos=( $DROPLET )
DROPLETIP="${dropletinfos[2]}"
echo "droplet IP=$DROPLETIP"

sleep 120

#DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"

echo "Before creating domainname=$domainname"
if [ ! -z "$domainname" ] && [ "$domainname" != "domainname" ]
  then 	
	#0) check and create domain and DNS 
	echo "Create domain domainname=$domainname"
	DOMAINCHECK=$(doctl compute domain get $domainname)
	echo "Check if domain $domainname exists: $DOMAINCHECK"
	if [ -z "$DOMAINCHECK" ]
		then
			echo "Create domain domainname=$domainname"
			DOMAINRES=$(doctl compute domain create $domainname --ip-address $DROPLETIP)
			echo "Created domain DOMAINRES=$DOMAINRES"
	fi	
  
	#check and delete existing domain DNS records www
	#1) doctl compute domain records list $domainname
	LIST=$(doctl compute domain records list $domainname | grep www | awk '{print $1}')
	#listinfo=( $LIST )
	#RECORDID="${listinfo[0]}"
	
	#2) doctl compute domain records delete $domainname record_id
	for recordid in $LIST; do
		echo "Delete old DNS record ID=$recordid"
		DELETERES=$(doctl compute domain records delete $domainname $recordid -v)
		#echo "DELETERES=$DELETERES"
	done
  
	#doctl compute domain create domain_name --ip-address droplet_ip_address
	#doctl compute domain records create $domainname --record-type A --record-name www --record data $DROPLETIP -v
	DOMAIN=$(doctl compute domain records create $domainname --record-type A --record-name www --record-data $DROPLETIP -v)
	echo "DOMAIN=$DOMAIN"
	DROPLETIP="www.$domainname"
  else
	echo "Do not create domain domainname=$domainname"
fi

if [ ! -z "$protocol" ] && [ "$protocol" = "https" ]
  then 	
	DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/https"
  else
    DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"
fi

echo "Trying to open a web browser... You can try to open a web browser manually and go to $DROPLETIPWEB"

xdg-open "$DROPLETIPWEB"




