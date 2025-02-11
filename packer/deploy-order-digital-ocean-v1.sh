#!/bin/bash
# ORDER installation script

#There are 3 files containing installation code: 
#1) This file deploy-order-digital-ocean.sh
#2) centos-install.sh
#3) order-packer-centos.json

#bash deploy-order-digital-ocean.sh 
#--token API-TOKEN-FROM-STEP-1 
#--os operational system: centos (default) or ubuntu
#-p parameters.yml 
#-dbuser - optional (default symfony)
#-dbpass - optional (default symfony)
#-protocol - optional (default http)
#--domainname domain_name.tld - optional
#--sslcertificate ssl_certificate.crt - optional
#--sslprivatekey intermediate_certificate.ca-crt
#--email - optional if sslcertificate=installcertbot


#os - centos or ubuntu
#apitoken=$1
#parameters=$2
#dbuser=$3
#dbpass=$4

#protocol=$5
#domainname=$6
#sslcertificate=$7
#sslprivatekey=$8

#Available images: https://do-community.github.io/available-images/


#$ bash deploy_test.sh --token apitoken --os centos --parameters parameters.yml --os alma9 --dbuser symfony --dbpass symfony --protocol http --domainname domainname --sslcertificate localhost.crt --sslprivatekey localhost.key
#$ bash deploy_test.sh --token apitoken --os centos --parameters parameters.yml --os alma9 --dbuser symfony --dbpass symfony --protocol https --domainname domainname --sslcertificate installcertbot --email email@example.com

COLOR='\033[1;36m'
NC='\033[0m' # No Color

f_install_certbot() {
  if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ] ]
      then
        #email='myemail@myemail.com'
        echo "Error: email is not provided for installcertbot option"
        echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
        exit 0
  fi
	if [ ! -z "$domainname" ] && [ ! -z "$protocol" ] && [ "$protocol" = "https" ]
		then
			echo -e ${COLOR} Install certbot on the Apache server ${NC}
			#bash /usr/local/bin/order-lab/packer/install-certbot.sh "$domainname" "$sslcertificate" "$email"
			#https://www.digitalocean.com/community/questions/run-shell-script-on-droplet-using-api
			echo -e ${COLOR} ssh root@ip 'bash -s' < ./usr/local/bin/order-lab/packer/install-certbot.sh ${NC}
			ssh root@"$ORIGDROPLETIP" 'bash -s' < ./usr/local/bin/order-lab/packer/install-certbot.sh
		else
			echo -e ${COLOR} Domain name is not provided: Do not install certbot on all OS ${NC}
	fi

	echo ""
	sleep 1
}

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
  -e|--email)
		email="$2"
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
    os='alma9'
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
echo "email=$email"

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

if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ]
  then
    #email='myemail@myemail.com'
    echo "Error: email is not provided for installcertbot option"
    echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
    exit 0
fi

#echo "Testing parameters: exit"
#exit 0

TESTING=true

if [ "$os" = "alma8" ]
  then 	
	ORDERPACKERJSON="order-packer-alma8.json"
	TESTING=false
fi
if [ "$os" = "alma9" ] || [ "$os" = "alma" ]
  then  
	ORDERPACKERJSON="order-packer-alma9.json"
	TESTING=false
fi
if [ "$os" = "centos7" ] || [ "$os" = "centos" ]
  then 	
	ORDERPACKERJSON="order-packer-centos7.json"
	TESTING=false
fi
if [ "$os" = "ubuntu22" ] || [ "$os" = "ubuntu" ]
  then 	
	ORDERPACKERJSON="order-packer-ubuntu22.json"
	TESTING=false
fi

#Optional OS
if [ "$os" = "alma8basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-alma8-basic-only.json"
fi
if [ "$os" = "alma9basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-alma9-basic-only.json"
fi
if [ "$os" = "alma9hcl" ]
  then  
	ORDERPACKERJSON="order-packer-alma9.json.pkr.hcl"
fi
if [ "$os" = "centosonly" ]
  then 	
	ORDERPACKERJSON="order-packer-centos-only.json"
fi
if [ "$os" = "centos7basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-centos7-basic-only.json"
fi
if [ "$os" = "centos-without-composer" ]
  then 	
	ORDERPACKERJSON="order-packer-centos-without-composer.json"
fi
if [ "$os" = "ubuntu22basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-ubuntu22-basic-only.json"
fi



#if [ -z "$ORDERPACKERJSON" ]
#then
#    echo "order-packer.json not found. Use default order-packer-ubuntu.json"
#	ORDERPACKERJSON="order-packer-ubuntu.json"
#fi

echo "ORDERPACKERJSON=$ORDERPACKERJSON"

if [ -f "$ORDERPACKERJSON" ]; then
    echo "$ORDERPACKERJSON exist"
else 
    echo "$ORDERPACKERJSON does not exist"
	exit 0;
fi


echo "*** Pre processing json file: replace and provide parameters ***"
sed -i -e "s/api_token_bash_value/$apitoken/g" "$ORDERPACKERJSON"
sed -i -e "s/parameters_bash_file/$parameters/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbpass/$dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_protocol/$protocol/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" parameters.yml
sed -i -e "s/bash_dbpass/$dbpass/g" parameters.yml

sed -i -e "s/bash_email/$email/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_domainname/$domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslcertificate/$sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslprivatekey/$sslprivatekey/g" "$ORDERPACKERJSON"

############ Run packer json file ############
echo "*** Building VM image from packer=[$ORDERPACKERJSON] ... ***"
#PACKEROUT=$(packer build "$ORDERPACKERJSON" | tail -1)
#echo "*** PACKEROUT=$PACKEROUT ***"
packer build "$ORDERPACKERJSON" | tee buildpacker.log
############ EOF Run packer json file ############

#--> digitalocean: A snapshot was created: 'packer-1642782038' (ID: 100353988) in regions 'nyc3'
#Use Packer v1.7.0 or later
echo "*** Building VM image from packer=[$ORDERPACKERJSON] ... ***"
LASTLINE=$(tail -1 buildpacker.log)
echo "*** Packer LASTLINE=$LASTLINE ***"
IMAGENAME=$(tail -1 buildpacker.log |grep -oP "(?<=created: ').*(?=' )")
IMAGEID=$(tail -1 buildpacker.log |grep -oP "(?<=ID: ).*(?=\))")
echo "image ID=$IMAGEID; name=$IMAGENAME"

echo "*** Sleep for 120 sec ***"
sleep 120

echo "*** Getting image ID ***"
echo "*** Doctl must be installed! https://www.digitalocean.com/docs/apis-clis/doctl/how-to/install/ ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed

#LASTLINE=$(doctl compute image list | tail -1)
#echo "LASTLINE=$LASTLINE"
#vars=( $LASTLINE )
#IMAGEID=${vars[0]}
#IMAGENAME=${vars[1]}
#echo "image ID=$IMAGEID; name=$IMAGENAME"

echo "*** Post processing json file ***"
sed -i -e "s/$apitoken/api_token_bash_value/g" "$ORDERPACKERJSON"
sed -i -e "s/$parameters/parameters_bash_file/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbpass/bash_dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/$protocol/bash_protocol/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" parameters.yml
sed -i -e "s/$dbpass/bash_dbpass/g" parameters.yml

sed -i -e "s/$email/bash_email/g" "$ORDERPACKERJSON"
sed -i -e "s/$domainname/bash_domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslcertificate/bash_sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslprivatekey/bash_sslprivatekey/g" "$ORDERPACKERJSON"


echo "*** Creating droplet ... ***"
DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)

#TESTING=true
#TESTING=false
echo "TESTING=$TESTING"
if [ "$TESTING" = true ]
  then
    echo -e ${COLOR} Testing="$TESTING" => Exit ${NC}
    exit 0
fi

echo "*** Starting firefox browser and creating admin user ***"
dropletinfos=( $DROPLET )
DROPLETIP="${dropletinfos[2]}"
echo "droplet IP=$DROPLETIP"
ORIGDROPLETIP="$DROPLETIP"
echo "original droplet IP=$ORIGDROPLETIP"

echo "*** Sleep for 120 sec ***"
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
  
	#check and delete existing domain DNS's A records with record 'www' or '@'
	#1) doctl compute domain records list $domainname
	LIST=$(doctl compute domain records list $domainname | grep -e '@' -e 'www' | grep -w A | awk '{print $1}')
	#listinfo=( $LIST )
	#RECORDID="${listinfo[0]}"
	
	#2) doctl compute domain records delete $domainname record_id. --force - Delete record without confirmation prompt
	for recordid in $LIST; do
		echo "Delete old DNS record ID=$recordid"
		DELETERES=$(doctl compute domain records delete $domainname $recordid --force -v)
		#echo "DELETERES=$DELETERES"
	done
  
	#doctl compute domain create domain_name --ip-address droplet_ip_address
	#'--record-name www' will create domain name with www prefix, i.e. www.view.online
	#'--record-name @' will create domain name without prefix, i.e. view.online
	#doctl compute domain records create $domainname --record-type A --record-name www --record data $DROPLETIP --record-ttl 30 -v
	#https://docs.digitalocean.com/reference/doctl/reference/compute/domain/records/update/
	#'doctl compute domain records create' or 'doctl compute domain records update': --record-ttl 	The record’s Time To Live value, in seconds, default: 1800
	DOMAIN=$(doctl compute domain records create $domainname --record-type A --record-name @ --record-ttl 60 --record-data $DROPLETIP -v)
	echo "DOMAIN=$DOMAIN"
	DROPLETIP="$domainname"
  else
	echo "Do not create domain domainname=$domainname"
fi

#We must install certbot after domain is created on the DigitalOcean.
#Run install-cerbot
#echo "Install certbot"
#f_install_certbot
#echo "*** Sleep for 60 sec after certbot ***"
echo -e ${COLOR} Sleep for 60 sec before open init web page ${NC}
sleep 60

if [ ! -z "$protocol" ] && [ "$protocol" = "https" ]
  then
    #if [ "$sslcertificate" = "installcertbot" && ! -z "$email" && ! -z "$domainname" ]
    #  then
	  #    DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/install-certbot/$email"
	  #  else
	  #    DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/https"
	  #fi
	  DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/https"
  else
    DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"
fi

echo "Trying to open a web browser in OS $OSTYPE... You can try to open a web browser manually and go to $DROPLETIPWEB"

#xdg-open "$DROPLETIPWEB"
if [[ "$OSTYPE" == "linux-gnu" ]]; then
    echo -e ${COLOR} Run browser in linux-gnu ${NC}
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "darwin" ]]; then
    # Mac OSX
		echo "open a web browser manually and go to $DROPLETIPWEB"
elif [[ "$OSTYPE" == "cygwin" ]]; then
    # POSIX compatibility layer and Linux environment emulation for Windows
    echo -e ${COLOR} Run browser in cygwin ${NC}
		#xdg-open Chrome --incognito "$DROPLETIPWEB"
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "msys" ]]; then
    # Lightweight shell and GNU utilities compiled for Windows (part of MinGW)
		echo -e ${COLOR} Run browser in msys ${NC}
		#start Chrome --incognito "$DROPLETIPWEB";
		start "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "win32" ]]; then
    # Windows
		echo -e ${COLOR} Run browser in win32 ${NC}
		#start Chrome --incognito "$DROPLETIPWEB";
		start "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "freebsd"* ]]; then
    echo -e ${COLOR} Run browser in freebsd ${NC}
		#xdg-open Chrome --incognito "$DROPLETIPWEB"
		xdg-open "$DROPLETIPWEB"
else
    # Unknown.
		echo "open a web browser manually and go to $DROPLETIPWEB"
fi #deploy-order-digital-ocean.sh: line 447: syntax error: unexpected end of file

